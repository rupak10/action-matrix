@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.create-card { background: var(--sl-surface); border: 1px solid var(--sl-border); border-radius: var(--sl-radius); max-width: 680px; margin: 0 auto; }
.create-card .card-header-area { padding: 1.5rem 2rem 1rem; border-bottom: 1px solid var(--sl-border); }
.create-card .card-body-area   { padding: 1.75rem 2rem; }
.form-label { font-size: .82rem; font-weight: 600; color: var(--sl-text); margin-bottom: .35rem; }
.form-control, .form-select { border-radius: 8px; border: 1px solid var(--sl-border-strong); font-size: .875rem; }
.form-control:focus, .form-select:focus { border-color: var(--sl-primary); box-shadow: 0 0 0 3px rgba(27,58,58,.08); }
.visit-type-card { border: 2px solid var(--sl-border); border-radius: 10px; padding: 1rem; cursor: pointer; transition: all .15s; text-align: center; }
.visit-type-card:hover { border-color: var(--sl-primary); background: var(--sl-primary-soft); }
.visit-type-card.selected { border-color: var(--sl-primary); background: var(--sl-primary-soft); }
.visit-type-card input[type=radio] { display: none; }
.visit-type-card .type-icon { font-size: 1.75rem; display: block; margin-bottom: .4rem; }
.visit-type-card .type-label { font-weight: 600; font-size: .875rem; color: var(--sl-text); }
.visit-type-card .type-desc  { font-size: .75rem; color: var(--sl-muted); }
</style>
@endpush

@section('content')
<div class="container-fluid pt-1 pb-4">

    {{-- Breadcrumb --}}
    <nav class="mb-3" style="font-size:.82rem">
        <a href="{{ route('visits.index') }}" class="text-decoration-none" style="color:var(--sl-muted)">
            <i class="bi bi-folder2-open me-1"></i>Visits
        </a>
        <span class="mx-2 text-sl-muted">/</span>
        <span class="fw-medium">New Visit</span>
    </nav>

    <div class="create-card shadow-sm">
        <div class="card-header-area">
            <h5 class="fw-bold mb-0">Create New Visit</h5>
            <div class="text-sl-muted mt-1" style="font-size:.82rem">
                Fill in the visit details. You can add observations after creating the visit.
            </div>
        </div>
        <div class="card-body-area">

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0" role="alert" style="font-size:.85rem;border-radius:8px">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please fix the errors below:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('visits.store') }}" method="POST" id="create-visit-form">
                @csrf

                {{-- Partner Organization --}}
                <div class="mb-4">
                    <label class="form-label" for="po_code">Partner Organization <span class="text-danger">*</span></label>
                    <select name="po_code" id="po_code" class="form-select @error('po_code') is-invalid @enderror" required>
                        <option value="">— Select PO —</option>
                        @foreach($poList as $po)
                        <option value="{{ $po->po_code }}" {{ old('po_code') === $po->po_code ? 'selected' : '' }}>
                            {{ $po->po_name }}{{ $po->po_short_name ? ' (' . $po->po_short_name . ')' : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('po_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Visit Category --}}
                <div class="mb-4">
                    <label class="form-label" for="visit_category">Visit Category <span class="text-danger">*</span></label>
                    <select name="visit_category" id="visit_category" class="form-select @error('visit_category') is-invalid @enderror" required>
                        <option value="">— Select Category —</option>
                        @foreach(['Regular Visit','Management Audit','Internal Audit','External Audit','Special Visit'] as $cat)
                        <option value="{{ $cat }}" {{ old('visit_category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('visit_category')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Visit Type --}}
                <div class="mb-4">
                    <label class="form-label" for="visit_type">Visit Type <span class="text-danger">*</span></label>
                    <select name="visit_type" id="visit_type" class="form-select @error('visit_type') is-invalid @enderror" required>
                        <option value="">— Select Type —</option>
                        <option value="ONSITE"  {{ old('visit_type', 'ONSITE') === 'ONSITE'  ? 'selected' : '' }}>Onsite</option>
                        <option value="OFFSITE" {{ old('visit_type') === 'OFFSITE' ? 'selected' : '' }}>Offsite</option>
                    </select>
                    @error('visit_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Letter Dates --}}
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label" for="letter_issue_date">Letter Issue Date <small class="fw-normal text-muted">(optional)</small></label>
                        <input type="text" name="letter_issue_date" id="letter_issue_date"
                               class="form-control datepicker @error('letter_issue_date') is-invalid @enderror"
                               value="{{ old('letter_issue_date') }}" placeholder="DD-MM-YYYY" autocomplete="off">
                        @error('letter_issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="letter_response_date">Response Deadline <small class="fw-normal text-muted">(optional)</small></label>
                        <input type="text" name="letter_response_date" id="letter_response_date"
                               class="form-control datepicker @error('letter_response_date') is-invalid @enderror"
                               value="{{ old('letter_response_date') }}" placeholder="DD-MM-YYYY" autocomplete="off">
                        @error('letter_response_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Visit Period --}}
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label" for="visit_from_date">From Date <span class="text-danger">*</span></label>
                        <input type="text" name="visit_from_date" id="visit_from_date"
                               class="form-control datepicker @error('visit_from_date') is-invalid @enderror"
                               value="{{ old('visit_from_date') }}" placeholder="DD-MM-YYYY" autocomplete="off" required>
                        @error('visit_from_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="visit_to_date">To Date <span class="text-danger">*</span></label>
                        <input type="text" name="visit_to_date" id="visit_to_date"
                               class="form-control datepicker @error('visit_to_date') is-invalid @enderror"
                               value="{{ old('visit_to_date') }}" placeholder="DD-MM-YYYY" autocomplete="off" required>
                        @error('visit_to_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Info note --}}
                <div class="alert border-0 mb-4" style="background:#f0f9ff;border-radius:8px;font-size:.82rem;color:#0369a1">
                    <i class="bi bi-info-circle me-2"></i>
                    After creating the visit you will be taken to the visit page where you can add observations one by one.
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;font-size:.875rem">Cancel</a>
                    <button type="submit" class="btn" style="background:var(--sl-primary);color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;padding:.45rem 1.5rem" id="btn-submit">
                        <i class="bi bi-folder-plus me-1"></i>Create Visit
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {
    // Optional letter date pickers
    flatpickr('#letter_issue_date',    { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y' });
    flatpickr('#letter_response_date', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y' });

    // Date pickers
    const fromPicker = flatpickr('#visit_from_date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd-m-Y',
        onChange: function (dates) {
            toPicker.set('minDate', dates[0] || null);
        }
    });
    const toPicker = flatpickr('#visit_to_date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd-m-Y',
    });

    // Select2 for PO
    $('#po_code').select2({ theme: 'bootstrap-5', placeholder: '— Select PO —' });

    // Prevent double submit
    $('#create-visit-form').on('submit', function () {
        $('#btn-submit').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Creating…'
        );
    });
})();
</script>
@endpush
