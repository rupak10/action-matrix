<?php

namespace App\Http\Controllers;

use App\Services\PdfService;

class ReportController extends Controller
{
    /** Roles that may access any report. */
    private const ALLOWED_ROLES = ['PKSF_CO', 'PKSF_SUPERVISOR', 'Admin', 'Super_Admin'];

    public function __construct(protected PdfService $pdf) {}

    /**
     * Report 1 — Full Action Matrix register (all records).
     */
    public function report1(): \Illuminate\Http\Response
    {
        $this->authorizeReport();

        // Fetch all matrices with creator name via a join
        $matrices = \Illuminate\Support\Facades\DB::table('acm_master as m')
            ->leftJoin('users as u', 'u.emp_id', '=', 'm.created_by')
            ->select(
                'm.acm_id',
                'm.po_code',
                'm.visiting_date',
                'm.observation_category',
                'm.priority',
                'm.status',
                'm.created_by',
                'u.name as created_by_name'
            )
            ->orderBy('m.acm_id')
            ->get();

        return $this->pdf->generate(
            view:     'reports.report1',
            data:     [
                'matrices'    => $matrices,
                'printedAt'   => now()->format('d M Y, h:i A'),
                'printedBy'   => auth()->user()->name,
            ],
            filename: 'action-matrix-register.pdf',
        );
    }

    /**
     * Abort with 403 if the authenticated user does not hold any of the
     * allowed reporting roles.
     */
    private function authorizeReport(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(self::ALLOWED_ROLES),
            403,
            'You do not have permission to access reports.'
        );
    }
}
