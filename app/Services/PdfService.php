<?php

namespace App\Services;

use Mpdf\Mpdf;

class PdfService
{
    /**
     * Generate a PDF from a Blade view and stream it to the browser.
     *
     * Font strategy
     * ─────────────
     * Primary (development): FreeSans — bundled with mPDF, covers the full
     *   Bengali Unicode block (U+0980–U+09FF). Good for testing.
     *
     * Production (recommended): SolaimanLipi — the standard Bangla font used
     *   by government and NGOs in Bangladesh.
     *   Download: https://www.omicronlab.com/bangla-fonts.html
     *   Place the TTF files at:
     *     storage/fonts/SolaimanLipi.ttf
     *     storage/fonts/SolaimanLipi_Bold.ttf
     *   Once present, PdfService auto-detects and switches to SolaimanLipi.
     *
     * @param  string  $view      Blade view name (e.g. 'reports.report1')
     * @param  array   $data      Data to pass to the view
     * @param  string  $filename  Output file name
     * @param  string  $format    Page size: 'A4', 'A4-L', etc.
     * @return \Illuminate\Http\Response
     */
    public function generate(
        string $view,
        array  $data     = [],
        string $filename = 'report.pdf',
        string $format   = 'A4'
    ): \Illuminate\Http\Response {

        $html = view($view, $data)->render();

        // ── mPDF instance ─────────────────────────────────────────────────
        $mpdf = new Mpdf([
            'mode'         => 'utf-8',
            'format'       => $format,
            'margin_top'   => 20,
            'margin_bottom'=> 20,
            'margin_left'  => 20,
            'margin_right' => 20,
            'default_font' => 'nikosh',
        ]);

        $mpdf->SetTitle(pathinfo($filename, PATHINFO_FILENAME));
        $mpdf->WriteHTML($html);

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
