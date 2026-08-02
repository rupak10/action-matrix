<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<style>
    @page {
        size: auto;
        margin-top: 165px;
        margin-bottom: 50px;
        odd-header-name: html_MyHeader1;
        odd-footer-name: html_MyFooter1;
    }

    body {
        font-family: 'nikosh', sans-serif;
        font-size: 10pt;
        color: #000000;
        background: #ffffff;
        margin: 0;
        line-height: 1.5;
    }

    .report-topbar {
        width: 100%;
        margin-bottom: 6px;
    }
    .report-topbar td.report-heading {
        font-size: 12pt;
        font-weight: bold;
    }
    .report-topbar td.print-date {
        text-align: right;
        font-size: 8pt;
        color: #444;
    }

    .filter-bar {
        font-size: 8.5pt;
        color: #444;
        margin-bottom: 10px;
    }

    .matrix-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
        margin-top: 4px;
    }
    .matrix-table thead td {
        background-color: #000000;
        color: #ffffff;
        padding: 6px 7px;
        border: 1px solid #000000;
        font-weight: normal;
        text-align: center;
    }
    .matrix-table tbody td {
        border: 1px solid #000000;
        padding: 5px 7px;
        vertical-align: top;
    }
    .matrix-table tbody tr:nth-child(even) td {
        background-color: #f2f2f2;
    }
    .text-center { text-align: center; }
</style>
</head>
<body>

{{-- ── PKSF Header image ────────────────────────────────────── --}}
<htmlpageheader name="MyHeader1">
    <div style="text-align:center;">
        <img src="{{ public_path('assets/img/pdf-header-bangla.jpg') }}" alt="Header" style="width:100%; height:auto;">
    </div>
</htmlpageheader>

{{-- ── PKSF Footer image ────────────────────────────────────── --}}
<htmlpagefooter name="MyFooter1">
    <div style="text-align:center;">
        <img src="{{ public_path('assets/img/pdf-footer-bangla.jpg') }}" alt="Footer" style="width:100%; height:auto;">
    </div>
</htmlpagefooter>

{{-- ── Top bar ───────────────────────────────────────────────── --}}
<table class="report-topbar">
    <tr>
        <td class="report-heading">Action Matrix Report</td>
        <td class="print-date">Print Date: {{ $printedAt }}</td>
    </tr>
</table>

{{-- ── Active filters ───────────────────────────────────────── --}}
@if(!empty($filters['po_code']) || !empty($filters['category']))
<div class="filter-bar">
    Filters:
    @if(!empty($filters['po_code']))
        PO: <strong>{{ $filters['po_code'] }}</strong>
    @endif
    @if(!empty($filters['category']))
        &nbsp;| Category: <strong>{{ $filters['category'] }}</strong>
    @endif
</div>
@endif

{{-- ── Matrix table ─────────────────────────────────────────── --}}
<table class="matrix-table">
    <thead>
        <tr>
            <td style="width:5%;">S/N</td>
            <td style="width:10%;">PO Code</td>
            <td style="width:13%;">Visit Date</td>
            <td style="width:25%;">Category</td>
            <td style="width:10%;">Priority</td>
            <td style="width:15%;">Status</td>
            <td style="width:14%;">Closed On</td>
        </tr>
    </thead>
    <tbody>
        @forelse($matrices as $i => $m)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ $m->po_code }}</td>
            <td class="text-center">{{ \Carbon\Carbon::parse($m->visiting_date)->format('d M Y') }}</td>
            <td>{{ $m->observation_category }}</td>
            <td class="text-center">{{ $m->priority }}</td>
            <td class="text-center">{{ ucwords(strtolower(str_replace('_', ' ', $m->status))) }}</td>
            <td class="text-center">{{ $m->updated_at ? \Carbon\Carbon::parse($m->updated_at)->format('d M Y') : '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center" style="padding: 20px;">
                No records found for the selected filters.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
