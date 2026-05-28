<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<style>
    /* ── Base ─────────────────────────────────────────────────── */
    body {
        font-family: 'freesans', sans-serif;
        font-size: 10pt;
        color: #000000;
        background: #ffffff;
        margin: 0;
        line-height: 1.5;
    }

    /* ── Top bar (date right, empty left) ─────────────────────── */
    .top-bar {
        width: 100%;
        margin-bottom: 6px;
    }
    .top-bar td {
        border: none;
        padding: 0;
    }
    .print-date {
        text-align: right;
        font-size: 9pt;
    }

    /* ── Centered header ──────────────────────────────────────── */
    .report-header {
        text-align: center;
        border-bottom: 2px solid #000000;
        padding-bottom: 10px;
        margin-bottom: 14px;
    }
    .org-name-bn {
        font-size: 14pt;
        font-weight: bold;
        margin: 0 0 2px 0;
    }
    .org-name-en {
        font-size: 10pt;
        margin: 0 0 8px 0;
    }
    .report-title-bn {
        font-size: 13pt;
        font-weight: bold;
        margin: 0 0 2px 0;
    }
    .report-title-en {
        font-size: 10pt;
        margin: 0;
    }

    /* ── Table ────────────────────────────────────────────────── */
    .matrix-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
        margin-top: 4px;
    }
    .matrix-table thead th {
        background-color: #000000;
        color: #ffffff;
        padding: 6px 7px;
        border: 1px solid #000000;
        font-weight: bold;
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

    /* ── Footer ───────────────────────────────────────────────── */
    .report-footer {
        margin-top: 16px;
        border-top: 1px solid #000000;
        padding-top: 5px;
        font-size: 8.5pt;
        text-align: center;
    }
</style>
</head>
<body>

{{-- ── Print date — top right ──────────────────────────────────── --}}
<table class="top-bar">
    <tr>
        <td></td>
        <td class="print-date">
            মুদ্রণের তারিখ / Print Date: <strong>{{ $printedAt }}</strong>
        </td>
    </tr>
</table>

{{-- ── Centered header ─────────────────────────────────────────── --}}
<div class="report-header">
    <p class="org-name-bn">পল্লী কর্ম-সহায়ক ফাউন্ডেশন (পিকেএসএফ)</p>
    <p class="org-name-en">Palli Karma-Sahayak Foundation (PKSF)</p>
    <p class="report-title-bn">অ্যাকশন ম্যাট্রিক্স তালিকা</p>
    <p class="report-title-en">Action Matrix Register</p>
</div>

{{-- ── Matrix table ─────────────────────────────────────────────── --}}
@php
    $statusMap = [
        'SAVED'               => 'সংরক্ষিত / Draft',
        'SUBMITTED'           => 'জমা দেওয়া হয়েছে / Submitted',
        'REJECTED'            => 'ফেরত দেওয়া হয়েছে / Returned',
        'PO_REVIEW'           => 'পিও পর্যালোচনা / PO Review',
        'PO_SUBMITTED'        => 'পিও জমা / PO Submitted',
        'PO_APPROVED'         => 'পিও অনুমোদিত / PO Approved',
        'PO_REJECTED'         => 'পিও কর্তৃক ফেরত / PO Returned',
        'WAITING_FOR_CLOSURE' => 'বন্ধের অপেক্ষায় / Awaiting Closure',
        'REVISION_REQUESTED'  => 'সংশোধন চাওয়া হয়েছে / Revision Requested',
        'PKSF_REJECTED'       => 'পিকেএসএফ কর্তৃক ফেরত / Returned by PKSF',
        'CLOSED'              => 'বন্ধ / Closed',
    ];

    $priorityMap = [
        'HIGH'   => 'উচ্চ / HIGH',
        'MEDIUM' => 'মাঝারি / MEDIUM',
        'LOW'    => 'নিম্ন / LOW',
    ];
@endphp

<table class="matrix-table">
    <thead>
        <tr>
            <th style="width:5%;">S/N</th>
            <th style="width:13%;">ACM ID</th>
            <th style="width:9%;">PO Code</th>
            <th style="width:12%;">Visit Date</th>
            <th style="width:16%;">Category</th>
            <th style="width:11%;">Priority</th>
            <th style="width:20%;">Status</th>
            <th style="width:14%;">Created By</th>
        </tr>
    </thead>
    <tbody>
        @forelse($matrices as $i => $m)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $m->acm_id }}</td>
            <td class="text-center">{{ $m->po_code }}</td>
            <td class="text-center">
                {{ \Carbon\Carbon::parse($m->visiting_date)->format('d M Y') }}
            </td>
            <td>{{ $m->observation_category }}</td>
            <td class="text-center">
                {{ $priorityMap[$m->priority] ?? $m->priority }}
            </td>
            <td>{{ $statusMap[$m->status] ?? $m->status }}</td>
            <td>{{ $m->created_by_name ?? $m->created_by }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center" style="padding: 20px;">
                কোনো রেকর্ড পাওয়া যায়নি। / No records found.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- ── Footer ──────────────────────────────────────────────────── --}}
<div class="report-footer">
    মোট রেকর্ড / Total Records: <strong>{{ count($matrices) }}</strong>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    তৈরি করেছেন / Generated by: <strong>{{ $printedBy }}</strong>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    পিকেএসএফ অ্যাকশন ম্যাট্রিক্স সিস্টেম / PKSF Action Matrix System
</div>

</body>
</html>
