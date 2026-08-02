<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class ReportController extends Controller
{
    private const ALLOWED_ROLES = ['PKSF_CO', 'PKSF_SUPERVISOR', 'Admin', 'Super_Admin'];

    private const CATEGORIES = ['FINANCIAL', 'OPERATIONAL', 'COMPLIANCE', 'GOVERNANCE'];

    public function report1(): \Illuminate\Http\Response
    {
        $this->authorizeReport();

        $matrices = DB::table('acm_master as m')
            ->select('m.po_code', 'm.visiting_date', 'm.observation_category', 'm.priority', 'm.status', 'm.updated_at')
            ->where('m.status', 'CLOSED')
            ->orderBy('m.visiting_date')
            ->get();

        return $this->streamPdf(
            view:     'reports.report1',
            data:     ['matrices' => $matrices, 'printedAt' => now()->format('d M Y, h:i A'), 'printedBy' => auth()->user()->name],
            filename: 'action-matrix-register.pdf',
            title:    'Action Matrix Register',
        );
    }

    public function actionMatrixFilter(): \Illuminate\View\View
    {
        $this->authorizeReport();

        $poList     = DB::table('acm_master')->distinct()->orderBy('po_code')->pluck('po_code');
        $categories = self::CATEGORIES;

        return view('reports.action_matrix_filter', compact('poList', 'categories'));
    }

    public function actionMatrixGenerate(Request $request): \Illuminate\Http\Response
    {
        $this->authorizeReport();

        $query = DB::table('acm_master')
            ->select('po_code', 'visiting_date', 'observation_category', 'priority', 'status', 'updated_at')
            ->where('status', 'CLOSED');

        if ($request->filled('po_code')) {
            $query->where('po_code', $request->po_code);
        }

        if ($request->filled('category')) {
            $query->where('observation_category', $request->category);
        }

        $matrices = $query->orderBy('visiting_date')->get();

        return $this->streamPdf(
            view:     'reports.report_action_matrix',
            data:     [
                'matrices'  => $matrices,
                'printedAt' => now()->format('d M Y, h:i A'),
                'filters'   => [
                    'po_code'  => $request->po_code,
                    'category' => $request->category,
                ],
            ],
            filename: 'action-matrix-report.pdf',
            title:    'Action Matrix Report',
        );
    }

    private function streamPdf(string $view, array $data, string $filename, string $title): \Illuminate\Http\Response
    {
        $html = view($view, $data)->render();

        $mpdf = new Mpdf([
            'format'        => 'A4',
            'orientation'   => 'p',
            'margin_left'   => 25,
            'margin_right'  => 25,
            'margin_top'    => 5,
            'margin_bottom' => 15,
            'default_font'  => 'nikosh',
            'mode'          => 'utf-8',
        ]);

        $mpdf->SetTitle($title);
        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$filename}\"",
            ]
        );
    }

    private function authorizeReport(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(self::ALLOWED_ROLES),
            403,
            'You do not have permission to access reports.'
        );
    }
}
