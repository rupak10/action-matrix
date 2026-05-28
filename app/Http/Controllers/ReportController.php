<?php

namespace App\Http\Controllers;

use App\Services\PdfService;

class ReportController extends Controller
{
    public function __construct(protected PdfService $pdf) {}

    /**
     * Report 1 — Bangla font test / template proof-of-concept.
     * Renders a sample PDF with mixed English and Bangla content.
     */
    public function report1(): \Illuminate\Http\Response
    {
        return $this->pdf->generate(
            view:     'reports.report1',
            data:     [
                'generatedAt' => now()->format('d M Y, h:i A'),
                'generatedBy' => auth()->user()->name,
            ],
            filename: 'report-1-bangla-test.pdf',
        );
    }
}
