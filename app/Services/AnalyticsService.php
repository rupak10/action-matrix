<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    // Human-readable status labels
    private const STATUS_LABELS = [
        'SAVED'               => 'Draft',
        'SUBMITTED'           => 'Submitted',
        'REJECTED'            => 'Returned',
        'PO_REVIEW'           => 'PO Review',
        'PO_SUBMITTED'        => 'PO Submitted',
        'PO_APPROVED'         => 'PO Approved',
        'PO_REJECTED'         => 'PO Returned',
        'WAITING_FOR_CLOSURE' => 'Awaiting Closure',
        'REVISION_REQUESTED'  => 'Revision Req.',
        'PKSF_REJECTED'       => 'PKSF Returned',
        'CLOSED'              => 'Closed',
    ];

    // ── Role helper ───────────────────────────────────────────────────────
    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Super_Admin']);
    }

    // ── Period helper ─────────────────────────────────────────────────────
    private function periodStart(string $period): ?Carbon
    {
        return match ($period) {
            '1month'  => now()->subMonth()->startOfDay(),
            '3months' => now()->subMonths(3)->startOfDay(),
            '6months' => now()->subMonths(6)->startOfDay(),
            '1year'   => now()->subYear()->startOfDay(),
            default   => null, // 'all' — no date restriction
        };
    }

    /**
     * Build the base scoped query for acm_master.
     *
     * Scoping rules:
     *   Admin / Super_Admin  → all matrices
     *   PKSF_CO / PKSF_SUPERVISOR → matrices they created
     *   PO_CO  / PO_SUPERVISOR    → matrices for their po_code
     */
    private function base(User $user, bool $isAdmin, string $period, string $poCode)
    {
        $q = DB::table('acm_master');

        if (!$isAdmin) {
            if ($user->isPksf()) {
                $q->where('created_by', $user->emp_id);
            } else {
                // PO user — own PO only
                $q->where('po_code', $user->po_code);
            }
        }

        // Period filter on creation date
        $start = $this->periodStart($period);
        if ($start) {
            $q->where('acm_master.created_at', '>=', $start);
        }

        // Optional PO code drill-down (Admin/PKSF only)
        if ($poCode !== '' && !$user->isPo()) {
            $q->where('po_code', $poCode);
        }

        return $q;
    }

    // ── Public entry point ────────────────────────────────────────────────

    public function getAnalytics(User $user, string $period, string $poCode): array
    {
        $isAdmin  = $this->isAdmin($user);
        $showPo   = $isAdmin || $user->isPksf();

        // $b() returns a fresh scoped Builder each call
        $b = fn () => $this->base($user, $isAdmin, $period, $poCode);

        return [
            'stats'          => $this->stats($b, $user),
            'statusChart'    => $this->statusChart($b),
            'priorityChart'  => $this->priorityChart($b),
            'categoryChart'  => $this->categoryChart($b),
            'monthlyChart'   => $this->monthlyChart($user, $isAdmin, $poCode),
            'poChart'        => $showPo ? $this->poChart($b) : null,
            'recentMatrices' => $this->recentMatrices($b),
            'longestPending' => $this->longestPending($b),
            'showPoChart'    => $showPo,
            'poList'         => $this->poList($user, $isAdmin),
        ];
    }

    // ── Stat cards ────────────────────────────────────────────────────────

    private function stats(callable $b, User $user): array
    {
        $total          = $b()->count();
        $open           = $b()->where('status', '!=', 'CLOSED')->count();
        $closed         = $b()->where('status', 'CLOSED')->count();
        $actionRequired = $b()->where('current_desk_emp_id', $user->emp_id)->count();

        // Avg days from matrix creation to first PO comment
        $avgDays = $b()
            ->joinSub(
                DB::table('acm_comments')
                    ->select('acm_id', DB::raw('MIN(created_at) as first_at'))
                    ->groupBy('acm_id'),
                'fc',
                'fc.acm_id', '=', 'acm_master.acm_id'
            )
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (fc.first_at::timestamp - acm_master.created_at::timestamp)) / 86400) as avg_days')
            ->value('avg_days');

        return [
            'total'           => $total,
            'open'            => $open,
            'closed'          => $closed,
            'action_required' => $actionRequired,
            'avg_days'        => round($avgDays ?? 0, 1),
        ];
    }

    // ── Status distribution (donut) ───────────────────────────────────────

    private function statusChart(callable $b): array
    {
        $rows = $b()
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => self::STATUS_LABELS[$r->status] ?? $r->status)
                             ->values()->toArray(),
            'data'   => $rows->pluck('cnt')->values()->toArray(),
            'raw'    => $rows->pluck('status')->values()->toArray(),
        ];
    }

    // ── Priority breakdown (bar) ──────────────────────────────────────────

    private function priorityChart(callable $b): array
    {
        $rows = $b()
            ->select('priority', DB::raw('COUNT(*) as cnt'))
            ->groupBy('priority')
            ->orderByRaw("CASE priority WHEN 'HIGH' THEN 1 WHEN 'MEDIUM' THEN 2 WHEN 'LOW' THEN 3 ELSE 4 END")
            ->get();

        return [
            'labels' => $rows->pluck('priority')->values()->toArray(),
            'data'   => $rows->pluck('cnt')->values()->toArray(),
        ];
    }

    // ── Category breakdown (bar) ──────────────────────────────────────────

    private function categoryChart(callable $b): array
    {
        $rows = $b()
            ->select('observation_category', DB::raw('COUNT(*) as cnt'))
            ->groupBy('observation_category')
            ->orderByDesc('cnt')
            ->get();

        return [
            'labels' => $rows->pluck('observation_category')->values()->toArray(),
            'data'   => $rows->pluck('cnt')->values()->toArray(),
        ];
    }

    // ── Monthly creation trend — always last 12 months ───────────────────

    private function monthlyChart(User $user, bool $isAdmin, string $poCode): array
    {
        $q = DB::table('acm_master');

        if (!$isAdmin) {
            $user->isPksf()
                ? $q->where('created_by', $user->emp_id)
                : $q->where('po_code', $user->po_code);
        }

        if ($poCode !== '' && !$user->isPo()) {
            $q->where('po_code', $poCode);
        }

        $rows = $q
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('EXTRACT(YEAR  FROM created_at)::int as yr'),
                DB::raw('EXTRACT(MONTH FROM created_at)::int as mo'),
                DB::raw('COUNT(*)                            as cnt')
            )
            ->groupBy('yr', 'mo')
            ->orderBy('yr')
            ->orderBy('mo')
            ->get()
            ->keyBy(fn ($r) => "{$r->yr}-{$r->mo}");

        $labels = [];
        $data   = [];

        for ($i = 11; $i >= 0; $i--) {
            $d        = now()->subMonths($i);
            $key      = $d->year . '-' . $d->month;
            $labels[] = $d->format('M Y');
            $data[]   = (int) ($rows->get($key)?->cnt ?? 0);
        }

        return compact('labels', 'data');
    }

    // ── Matrices by PO code — top 10 (Admin/PKSF only) ───────────────────

    private function poChart(callable $b): array
    {
        $rows = $b()
            ->select('po_code', DB::raw('COUNT(*) as cnt'))
            ->groupBy('po_code')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->pluck('po_code')->values()->toArray(),
            'data'   => $rows->pluck('cnt')->values()->toArray(),
        ];
    }

    // ── Recent 8 matrices ─────────────────────────────────────────────────

    private function recentMatrices(callable $b): array
    {
        return $b()
            ->select('acm_id', 'po_code', 'status', 'priority', 'created_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($m) => [
                'acm_id'     => $m->acm_id,
                'po_code'    => $m->po_code,
                'status'     => self::STATUS_LABELS[$m->status] ?? $m->status,
                'status_raw' => $m->status,
                'priority'   => $m->priority,
                'created_at' => Carbon::parse($m->created_at)->format('d M Y'),
            ])
            ->values()->toArray();
    }

    // ── Top 8 longest-pending (non-closed) ───────────────────────────────

    private function longestPending(callable $b): array
    {
        return $b()
            ->where('status', '!=', 'CLOSED')
            ->select(
                'acm_id', 'po_code', 'status', 'priority',
                DB::raw('EXTRACT(EPOCH FROM (NOW() - acm_master.created_at))::int / 86400 as days_pending')
            )
            ->orderByDesc('days_pending')
            ->limit(8)
            ->get()
            ->map(fn ($m) => [
                'acm_id'       => $m->acm_id,
                'po_code'      => $m->po_code,
                'status'       => self::STATUS_LABELS[$m->status] ?? $m->status,
                'status_raw'   => $m->status,
                'priority'     => $m->priority,
                'days_pending' => (int) $m->days_pending,
            ])
            ->values()->toArray();
    }

    // ── PO list for dropdown ─────────────────────────────────────────────

    public function poList(User $user, bool $isAdmin): array
    {
        $q = DB::table('acm_master')->distinct()->select('po_code');

        if (!$isAdmin && $user->isPksf()) {
            $q->where('created_by', $user->emp_id);
        }

        return $q->orderBy('po_code')->pluck('po_code')->toArray();
    }
}
