@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="fw-bold mb-0" style="color:#1e3a5f;">Action Matrix Reports</h3>
                    <p class="text-muted small mb-0">Select filters and generate the report.</p>
                </div>
                <a href="{{ route('action-matrix.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('reports.action-matrix-report.generate') }}" method="GET" target="_blank">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Partner Organization (PO)</label>
                            <select name="po_code" class="form-select form-select-sm">
                                <option value="">— All POs —</option>
                                @foreach($poList as $code)
                                    <option value="{{ $code }}">{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Observation Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">— All Categories —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-sm py-2" style="background:#1e3a5f;color:#fff;border-radius:8px;">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Generate Report
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
