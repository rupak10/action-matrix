<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class VisitQueryService
{
    // ── Base visibility query ──────────────────────────────────────────────

    public function baseVisitQuery(User $user)
    {
        $q = DB::table('acm_visits');

        if ($user->hasAnyRole(['Super_Admin', 'Super Admin', 'Admin'])) {
            return $q; // full access
        }

        if ($user->isSeniorManagement()) {
            // SM_MD: all visits past SAVED
            if ($user->isSmMd()) {
                return $q->whereNotIn('acm_visits.status', ['SAVED']);
            }
            // SM_DMD / SM_SGM: assigned PO codes past SAVED
            $poCodes = $user->assignedPoCodes();
            return $q->whereIn('acm_visits.po_code', $poCodes)->whereNotIn('acm_visits.status', ['SAVED']);
        }

        $q->where(function ($inner) use ($user) {
            // Always see visits they created or that are on their desk
            $inner->where('acm_visits.created_by', $user->emp_id)
                  ->orWhere('acm_visits.current_desk_emp_id', $user->emp_id);

            $isSupervisor = DB::table('user_supervisors')
                ->where('supervisor_emp_id', $user->emp_id)
                ->exists();

            if ($user->isPksf() && $isSupervisor) {
                // PKSF Supervisor: all visits past SAVED, except REJECTED (those are back with the CO)
                $inner->orWhereNotIn('acm_visits.status', ['SAVED', 'REJECTED']);
            }

            if ($user->isPo()) {
                if ($user->po_code) {
                    $inner->orWhere(function ($q2) use ($user, $isSupervisor) {
                        $q2->where('acm_visits.po_code', $user->po_code);
                        if ($isSupervisor) {
                            // PO SO: sees from PO_SO_REVIEW onwards
                            $q2->whereIn('acm_visits.status', ['PO_SO_REVIEW', 'PO_REVIEW', 'PO_SUBMITTED', 'PO_APPROVED']);
                        } else {
                            // PO CO: only from PO_REVIEW onwards (PO_SO_REVIEW is SO's stage, CO not involved yet)
                            $q2->whereIn('acm_visits.status', ['PO_REVIEW', 'PO_SUBMITTED', 'PO_APPROVED']);
                        }
                    });
                }
            }
        });

        return $q;
    }

    // ── Stat counts ────────────────────────────────────────────────────────

    public function getStatCounts(User $user): array
    {
        $base = $this->baseVisitQuery($user);

        $total          = (clone $base)->count();
        $actionRequired = (clone $base)->where('acm_visits.current_desk_emp_id', $user->emp_id)->count();
        $inProgress     = (clone $base)->whereNotIn('acm_visits.status', ['SAVED'])->count();

        // Pending observations count — only for visits visible to this user
        $visitIds = (clone $base)->pluck('id');
        $pendingObservations = DB::table('acm_observations')
            ->whereIn('visit_id', $visitIds)
            ->where('resolution_status', 'PENDING_RESOLVED')
            ->count();

        return [
            'total'               => $total,
            'action_required'     => $actionRequired,
            'in_progress'         => $inProgress,
            'pending_observations'=> $pendingObservations,
        ];
    }

    // ── DataTables server-side ─────────────────────────────────────────────

    public function getVisitsTableData(array $dtParams, array $filters, User $user): array
    {
        $base = $this->baseVisitQuery($user);

        // Join po_info for PO name
        $base->leftJoin('po_info', 'acm_visits.po_code', '=', 'po_info.po_code')
             ->select(
                 'acm_visits.id',
                 'acm_visits.visit_code',
                 'acm_visits.po_code',
                 'po_info.po_name',
                 'po_info.po_short_name',
                 'acm_visits.visit_from_date',
                 'acm_visits.visit_to_date',
                 'acm_visits.visit_type',
                 'acm_visits.status',
                 'acm_visits.current_desk_emp_id',
                 'acm_visits.created_by',
                 'acm_visits.created_at',
             );

        // ── View filter ───────────────────────────────────────────────────
        switch ($filters['view'] ?? 'all') {
            case 'action_required':
                $base->where('acm_visits.current_desk_emp_id', $user->emp_id);
                break;
            case 'created_by_me':
                $base->where('acm_visits.created_by', $user->emp_id);
                break;
        }

        // ── PO filter ──────────────────────────────────────────────────────
        if (!empty($filters['po_code'])) {
            $base->where('acm_visits.po_code', $filters['po_code']);
        }

        // ── Visit type filter ──────────────────────────────────────────────
        if (!empty($filters['visit_type'])) {
            $base->where('acm_visits.visit_type', $filters['visit_type']);
        }

        // ── Status filter ──────────────────────────────────────────────────
        if (!empty($filters['status'])) {
            $base->where('acm_visits.status', $filters['status']);
        }

        $recordsTotal = (clone $base)->count();

        // ── Global search ──────────────────────────────────────────────────
        $search = trim($dtParams['search']['value'] ?? '');
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('acm_visits.visit_code', 'ilike', "%{$search}%")
                  ->orWhere('acm_visits.po_code', 'ilike', "%{$search}%")
                  ->orWhere('po_info.po_name', 'ilike', "%{$search}%")
                  ->orWhere('acm_visits.status', 'ilike', "%{$search}%")
                  ->orWhere('acm_visits.visit_type', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        // ── Sorting ────────────────────────────────────────────────────────
        $columnMap = [
            0 => 'acm_visits.visit_code',
            1 => 'acm_visits.po_code',
            2 => 'acm_visits.visit_from_date',
            3 => 'acm_visits.visit_type',
            4 => 'acm_visits.status',
        ];
        $orderCol = $columnMap[$dtParams['order'][0]['column'] ?? 0] ?? 'acm_visits.id';
        $orderDir = ($dtParams['order'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $base->orderBy($orderCol, $orderDir);

        // ── Pagination ─────────────────────────────────────────────────────
        $start  = (int) ($dtParams['start'] ?? 0);
        $length = (int) ($dtParams['length'] ?? 25);
        $rows   = (clone $base)->skip($start)->take($length)->get();

        // ── Enrich with observation counts ─────────────────────────────────
        $visitIds = $rows->pluck('id');
        $obsCounts = DB::table('acm_observations')
            ->whereIn('visit_id', $visitIds)
            ->selectRaw('visit_id, resolution_status, count(*) as cnt')
            ->groupBy('visit_id', 'resolution_status')
            ->get()
            ->groupBy('visit_id');

        // ── Incoming movement (who sent it here) ───────────────────────────
        $lastMovements = DB::table('acm_visit_movements')
            ->whereIn('visit_id', $visitIds)
            ->orderByDesc('id')
            ->get()
            ->unique('visit_id')
            ->keyBy('visit_id');

        // ── Resolve employee names ─────────────────────────────────────────
        $empIds = collect();
        foreach ($rows as $row) {
            $empIds->push($row->current_desk_emp_id);
            if ($mov = $lastMovements->get($row->id)) {
                $empIds->push($mov->from_emp_id);
            }
        }
        $users = DB::table('users')
            ->whereIn('emp_id', $empIds->filter()->unique()->values())
            ->select('emp_id', 'name', 'designation')
            ->get()
            ->keyBy('emp_id');

        $data = $rows->map(function ($row) use ($obsCounts, $lastMovements, $users, $user) {
            $counts  = $obsCounts->get($row->id, collect());
            $total   = $counts->sum('cnt');
            $resolved = $counts->where('resolution_status', 'RESOLVED')->sum('cnt');
            $pending  = $counts->where('resolution_status', 'PENDING_RESOLVED')->sum('cnt');

            $lastMov  = $lastMovements->get($row->id);
            $fromUser = $lastMov ? $users->get($lastMov->from_emp_id) : null;
            $deskUser = $users->get($row->current_desk_emp_id);

            return [
                'id'                  => $row->id,
                'visit_code'          => $row->visit_code,
                'po_code'             => $row->po_code,
                'po_name'             => $row->po_short_name ?? $row->po_name ?? $row->po_code,
                'visit_from_date'     => $row->visit_from_date,
                'visit_to_date'       => $row->visit_to_date,
                'visit_type'          => $row->visit_type,
                'status'              => $row->status,
                'observations_total'  => $total,
                'observations_resolved' => $resolved,
                'observations_pending'  => $pending,
                'current_desk'        => $deskUser?->name ?? $row->current_desk_emp_id,
                'incoming_from'       => $fromUser?->name ?? null,
                'last_action'         => $lastMov?->action_type ?? null,
                'is_my_desk'          => $row->current_desk_emp_id === $user->emp_id,
                'created_at'          => $row->created_at,
            ];
        });

        return [
            'draw'            => (int) ($dtParams['draw'] ?? 1),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values(),
        ];
    }
}
