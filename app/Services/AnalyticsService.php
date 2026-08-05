<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    private const STATUS_LABELS = [
        'SAVED'        => 'Draft',
        'SUBMITTED'    => 'At PKSF CO',
        'REJECTED'     => 'Returned',
        'PO_SO_REVIEW' => 'Sent to PO',
        'PO_REVIEW'    => 'PO CO Review',
        'PO_SUBMITTED' => 'PO SO Review',
        'PO_APPROVED'  => 'Completed',
    ];

    private const RESOLUTION_LABELS = [
        'OPEN'             => 'Open',
        'PENDING_RESOLVED' => 'Pending Resolution',
        'RESOLVED'         => 'Resolved',
    ];

    // ── Scope: PO codes visible to this user ─────────────────────────────

    private function scopedPoCodes(User $user): ?array
    {
        // SM_MD and admins — unrestricted
        if ($user->hasAnyRole(['Super_Admin', 'Admin', 'SM_MD'])) {
            return null;
        }

        // PO users — only their own PO
        if ($user->isPo()) {
            return array_filter([$user->po_code]);
        }

        // PKSF_CO, PKSF_SUPERVISOR, SM_DMD, SM_SGM — assigned POs
        return DB::table('user_po_assignments')
            ->where('emp_id', $user->emp_id)
            ->distinct()
            ->pluck('po_code')
            ->toArray();
    }

    private function periodStart(string $period): ?Carbon
    {
        return match ($period) {
            '1month'  => now()->subMonth()->startOfDay(),
            '3months' => now()->subMonths(3)->startOfDay(),
            '6months' => now()->subMonths(6)->startOfDay(),
            '1year'   => now()->subYear()->startOfDay(),
            default   => null,
        };
    }

    // ── Base query builders ───────────────────────────────────────────────

    private function base(?array $poCodes, string $period, string $poCode)
    {
        $q = DB::table('acm_visits');

        if ($poCodes !== null) {
            $q->whereIn('po_code', $poCodes);
        }

        $start = $this->periodStart($period);
        if ($start) {
            $q->where('acm_visits.created_at', '>=', $start);
        }

        if ($poCode !== '') {
            $q->where('acm_visits.po_code', $poCode);
        }

        return $q;
    }

    private function baseObs(?array $poCodes, string $period, string $poCode)
    {
        $q = DB::table('acm_observations')
            ->join('acm_visits', 'acm_visits.id', '=', 'acm_observations.visit_id');

        if ($poCodes !== null) {
            $q->whereIn('acm_visits.po_code', $poCodes);
        }

        $start = $this->periodStart($period);
        if ($start) {
            $q->where('acm_observations.created_at', '>=', $start);
        }

        if ($poCode !== '') {
            $q->where('acm_visits.po_code', $poCode);
        }

        return $q;
    }

    // ── Public entry point ────────────────────────────────────────────────

    public function getAnalytics(User $user, string $period, string $poCode): array
    {
        $poCodes     = $this->scopedPoCodes($user);
        $showPoPanel = !$user->isPo();

        $b    = fn () => $this->base($poCodes, $period, $poCode);
        $bObs = fn () => $this->baseObs($poCodes, $period, $poCode);

        return [
            'stats'              => $this->stats($b, $bObs, $user),
            'statusChart'        => $this->statusChart($b),
            'obsResolutionChart' => $this->obsResolutionChart($bObs),
            'priorityChart'      => $this->priorityChart($bObs),
            'categoryChart'      => $this->categoryChart($bObs),
            'monthlyChart'       => $this->monthlyChart($poCodes, $poCode),
            'poComparisonChart'  => $showPoPanel ? $this->poComparisonChart($bObs) : null,
            'recentVisits'       => $this->recentVisits($b),
            'longestPending'     => $this->longestPending($b),
            'showPoPanel'        => $showPoPanel,
            'poList'             => $this->poList($user, $poCodes),
        ];
    }

    // ── Stat cards ────────────────────────────────────────────────────────

    private function stats(callable $b, callable $bObs, User $user): array
    {
        $total     = $b()->count();
        $completed = $b()->where('status', 'PO_APPROVED')->count();
        $open      = $total - $completed;

        $obsTotal     = $bObs()->count();
        $obsResolved  = $bObs()->where('acm_observations.resolution_status', 'RESOLVED')->count();
        $obsPending   = $bObs()->where('acm_observations.resolution_status', 'PENDING_RESOLVED')->count();
        $actionMatrix = $bObs()->where('acm_observations.action_matrix', 'Y')->count();

        $avgDays = $b()
            ->where('status', 'PO_APPROVED')
            ->selectRaw("ROUND(AVG(EXTRACT(EPOCH FROM (updated_at - created_at)) / 86400)::numeric, 1) as avg_days")
            ->value('avg_days');

        $onMyDesk = $user->isPo()
            ? null
            : $b()->where('current_desk_emp_id', $user->emp_id)->count();

        return [
            'total'         => $total,
            'open'          => $open,
            'completed'     => $completed,
            'obs_total'     => $obsTotal,
            'obs_resolved'  => $obsResolved,
            'obs_pending'   => $obsPending,
            'action_matrix' => $actionMatrix,
            'avg_days'      => (float) ($avgDays ?? 0),
            'on_my_desk'    => $onMyDesk,
        ];
    }

    // ── Visit status donut ────────────────────────────────────────────────

    private function statusChart(callable $b): array
    {
        $rows = $b()
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => self::STATUS_LABELS[$r->status] ?? $r->status)->values()->toArray(),
            'data'   => $rows->pluck('cnt')->values()->toArray(),
            'raw'    => $rows->pluck('status')->values()->toArray(),
        ];
    }

    // ── Observation resolution donut ──────────────────────────────────────

    private function obsResolutionChart(callable $bObs): array
    {
        $rows = $bObs()
            ->select('acm_observations.resolution_status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('acm_observations.resolution_status')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => self::RESOLUTION_LABELS[$r->resolution_status] ?? $r->resolution_status)->values()->toArray(),
            'data'   => $rows->pluck('cnt')->values()->toArray(),
            'raw'    => $rows->pluck('resolution_status')->values()->toArray(),
        ];
    }

    // ── Priority bar ──────────────────────────────────────────────────────

    private function priorityChart(callable $bObs): array
    {
        $rows = $bObs()
            ->select('acm_observations.priority', DB::raw('COUNT(*) as cnt'))
            ->groupBy('acm_observations.priority')
            ->orderByRaw("CASE acm_observations.priority WHEN 'HIGH' THEN 1 WHEN 'MEDIUM' THEN 2 WHEN 'LOW' THEN 3 ELSE 4 END")
            ->get();

        return [
            'labels' => $rows->pluck('priority')->values()->toArray(),
            'data'   => $rows->pluck('cnt')->values()->toArray(),
        ];
    }

    // ── Category horizontal bar ───────────────────────────────────────────

    private function categoryChart(callable $bObs): array
    {
        $rows = $bObs()
            ->select('acm_observations.observation_category', DB::raw('COUNT(*) as cnt'))
            ->groupBy('acm_observations.observation_category')
            ->orderByDesc('cnt')
            ->get();

        return [
            'labels' => $rows->pluck('observation_category')->values()->toArray(),
            'data'   => $rows->pluck('cnt')->values()->toArray(),
        ];
    }

    // ── Monthly trend — always last 12 months, ignores period filter ──────

    private function monthlyChart(?array $poCodes, string $poCode): array
    {
        $startMonth = now()->subMonths(11)->startOfMonth();

        $vq = DB::table('acm_visits');
        if ($poCodes !== null) $vq->whereIn('po_code', $poCodes);
        if ($poCode !== '') $vq->where('po_code', $poCode);

        $visitRows = $vq->where('created_at', '>=', $startMonth)
            ->selectRaw('EXTRACT(YEAR FROM created_at)::int as yr, EXTRACT(MONTH FROM created_at)::int as mo, COUNT(*) as cnt')
            ->groupBy('yr', 'mo')
            ->get()
            ->keyBy(fn ($r) => "{$r->yr}-{$r->mo}");

        $oq = DB::table('acm_observations')
            ->join('acm_visits', 'acm_visits.id', '=', 'acm_observations.visit_id');
        if ($poCodes !== null) $oq->whereIn('acm_visits.po_code', $poCodes);
        if ($poCode !== '') $oq->where('acm_visits.po_code', $poCode);

        $obsRows = $oq->where('acm_observations.created_at', '>=', $startMonth)
            ->selectRaw('EXTRACT(YEAR FROM acm_observations.created_at)::int as yr, EXTRACT(MONTH FROM acm_observations.created_at)::int as mo, COUNT(*) as cnt')
            ->groupBy('yr', 'mo')
            ->get()
            ->keyBy(fn ($r) => "{$r->yr}-{$r->mo}");

        $labels = $visits = $observations = [];

        for ($i = 11; $i >= 0; $i--) {
            $d              = now()->subMonths($i);
            $key            = $d->year . '-' . $d->month;
            $labels[]       = $d->format('M y');
            $visits[]       = (int) ($visitRows->get($key)?->cnt ?? 0);
            $observations[] = (int) ($obsRows->get($key)?->cnt ?? 0);
        }

        return compact('labels', 'visits', 'observations');
    }

    // ── PO comparison: open vs resolved observations per PO ──────────────

    private function poComparisonChart(callable $bObs): array
    {
        $open = $bObs()
            ->where('acm_observations.resolution_status', 'OPEN')
            ->select('acm_visits.po_code', DB::raw('COUNT(*) as cnt'))
            ->groupBy('acm_visits.po_code')
            ->get()
            ->keyBy('po_code');

        $resolved = $bObs()
            ->where('acm_observations.resolution_status', 'RESOLVED')
            ->select('acm_visits.po_code', DB::raw('COUNT(*) as cnt'))
            ->groupBy('acm_visits.po_code')
            ->get()
            ->keyBy('po_code');

        $poCodes = $open->keys()->merge($resolved->keys())->unique()
            ->sortByDesc(fn ($p) => ($open[$p]?->cnt ?? 0) + ($resolved[$p]?->cnt ?? 0))
            ->take(12)->values()->toArray();

        return [
            'labels'   => $poCodes,
            'open'     => array_map(fn ($p) => (int) ($open[$p]?->cnt ?? 0), $poCodes),
            'resolved' => array_map(fn ($p) => (int) ($resolved[$p]?->cnt ?? 0), $poCodes),
        ];
    }

    // ── Recent 8 visits ───────────────────────────────────────────────────

    private function recentVisits(callable $b): array
    {
        return $b()
            ->select('id', 'visit_code', 'po_code', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($v) => [
                'id'         => $v->id,
                'visit_code' => $v->visit_code,
                'po_code'    => $v->po_code,
                'status'     => self::STATUS_LABELS[$v->status] ?? $v->status,
                'status_raw' => $v->status,
                'created_at' => Carbon::parse($v->created_at)->format('d M Y'),
            ])
            ->values()->toArray();
    }

    // ── Longest pending 8 ─────────────────────────────────────────────────

    private function longestPending(callable $b): array
    {
        return $b()
            ->where('status', '!=', 'PO_APPROVED')
            ->select(
                'id', 'visit_code', 'po_code', 'status',
                DB::raw('EXTRACT(EPOCH FROM (NOW() - created_at))::int / 86400 as days_pending')
            )
            ->orderByDesc('days_pending')
            ->limit(8)
            ->get()
            ->map(fn ($v) => [
                'id'           => $v->id,
                'visit_code'   => $v->visit_code,
                'po_code'      => $v->po_code,
                'status'       => self::STATUS_LABELS[$v->status] ?? $v->status,
                'status_raw'   => $v->status,
                'days_pending' => (int) $v->days_pending,
            ])
            ->values()->toArray();
    }

    // ── PO dropdown list ──────────────────────────────────────────────────

    public function poList(User $user, ?array $poCodes): array
    {
        if ($user->isPo()) return [];

        if ($poCodes === null) {
            return DB::table('user_po_assignments')
                ->distinct()->orderBy('po_code')->pluck('po_code')->toArray();
        }

        return collect($poCodes)->sort()->values()->toArray();
    }
}
