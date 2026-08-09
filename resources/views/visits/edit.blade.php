@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.edit-card { background: var(--sl-surface); border: 1px solid var(--sl-border); border-radius: var(--sl-radius); max-width: 680px; margin: 0 auto; }
.edit-card .card-header-area { padding: 1.5rem 2rem 1rem; border-bottom: 1px solid var(--sl-border); }
.edit-card .card-body-area   { padding: 1.75rem 2rem; }
.form-label { font-size: .82rem; font-weight: 600; color: var(--sl-text); margin-bottom: .35rem; }
.form-control, .form-select { border-radius: 8px; border: 1px solid var(--sl-border-strong); font-size: .875rem; }
.form-control:focus, .form-select:focus { border-color: var(--sl-primary); box-shadow: 0 0 0 3px rgba(27,58,58,.08); }
.visit-type-card { border: 2px solid var(--sl-border); border-radius: 10px; padding: 1rem; cursor: pointer; transition: all .15s; text-align: center; }
.visit-type-card.selected { border-color: var(--sl-primary); background: var(--sl-primary-soft); }
.visit-type-card input[type=radio] { display: none; }
.visit-type-card .type-icon { font-size: 1.75rem; display: block; margin-bottom: .4rem; }
.visit-type-card .type-label { font-weight: 600; font-size: .875rem; color: var(--sl-text); }
</style>
@endpush

@section('content')
<div class="container-fluid pt-1 pb-4">

    <nav class="mb-3" style="font-size:.82rem">
        <a href="{{ route('visits.index') }}" class="text-decoration-none" style="color:var(--sl-muted)">Visits</a>
        <span class="mx-2 text-sl-muted">/</span>
        <a href="{{ route('visits.show', $visit->id) }}" class="text-decoration-none" style="color:var(--sl-muted)">{{ $visit->visit_code }}</a>
        <span class="mx-2 text-sl-muted">/</span>
        <span class="fw-medium">Edit</span>
    </nav>

    <div class="edit-card shadow-sm">
        <div class="card-header-area">
            <h5 class="fw-bold mb-0">Edit Visit &mdash; {{ $visit->visit_code }}</h5>
            <div class="text-sl-muted mt-1" style="font-size:.82rem">Update visit dates or type. PO cannot be changed after creation.</div>
        </div>
        <div class="card-body-area">

            @if($errors->any())
            <div class="alert alert-danger border-0 alert-dismissible fade show" style="font-size:.85rem;border-radius:8px">
                <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('visits.update', $visit->id) }}" method="POST" id="edit-visit-form">
                @csrf
                @method('PUT')

                {{-- PO (read-only display) --}}
                <div class="mb-4">
                    <label class="form-label">Partner Organization</label>
                    <div class="form-control bg-light" style="border-radius:8px;color:var(--sl-muted)">
                        {{ $visit->poInfo?->po_name ?? $visit->po_code }}
                    </div>
                    <div class="form-text">PO cannot be changed after the visit is created.</div>
                </div>

                {{-- Visit Category --}}
                <div class="mb-4">
                    <label class="form-label" for="visit_category">Visit Category <span class="text-danger">*</span></label>
                    <select name="visit_category" id="visit_category" class="form-select @error('visit_category') is-invalid @enderror" required>
                        <option value="">— Select Category —</option>
                        @foreach(['Regular Visit','Management Audit','Internal Audit','External Audit','Special Visit'] as $cat)
                        <option value="{{ $cat }}" {{ old('visit_category', $visit->visit_category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('visit_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="visit_type">Visit Type <span class="text-danger">*</span></label>
                    <select name="visit_type" id="visit_type" class="form-select @error('visit_type') is-invalid @enderror" required>
                        <option value="">— Select Type —</option>
                        <option value="ONSITE"  {{ old('visit_type', $visit->visit_type) === 'ONSITE'  ? 'selected' : '' }}>Onsite</option>
                        <option value="OFFSITE" {{ old('visit_type', $visit->visit_type) === 'OFFSITE' ? 'selected' : '' }}>Offsite</option>
                    </select>
                    @error('visit_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Letter Dates --}}
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label" for="letter_issue_date">Letter Issue Date <small class="fw-normal text-muted">(optional)</small></label>
                        <input type="text" name="letter_issue_date" id="letter_issue_date"
                               class="form-control datepicker @error('letter_issue_date') is-invalid @enderror"
                               value="{{ old('letter_issue_date', $visit->letter_issue_date?->format('Y-m-d')) }}"
                               placeholder="DD-MM-YYYY" autocomplete="off">
                        @error('letter_issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="letter_response_date">Response Deadline <small class="fw-normal text-muted">(optional)</small></label>
                        <input type="text" name="letter_response_date" id="letter_response_date"
                               class="form-control datepicker @error('letter_response_date') is-invalid @enderror"
                               value="{{ old('letter_response_date', $visit->letter_response_date?->format('Y-m-d')) }}"
                               placeholder="DD-MM-YYYY" autocomplete="off">
                        @error('letter_response_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label" for="visit_from_date">From Date <span class="text-danger">*</span></label>
                        <input type="text" name="visit_from_date" id="visit_from_date"
                               class="form-control datepicker @error('visit_from_date') is-invalid @enderror"
                               value="{{ old('visit_from_date', $visit->visit_from_date) }}" autocomplete="off" required>
                        @error('visit_from_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="visit_to_date">To Date <span class="text-danger">*</span></label>
                        <input type="text" name="visit_to_date" id="visit_to_date"
                               class="form-control datepicker @error('visit_to_date') is-invalid @enderror"
                               value="{{ old('visit_to_date', $visit->visit_to_date) }}" autocomplete="off" required>
                        @error('visit_to_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('visits.show', $visit->id) }}" class="btn btn-outline-secondary" style="border-radius:8px;font-size:.875rem">Cancel</a>
                    <button type="submit" class="btn" style="background:var(--sl-primary);color:#fff;border-radius:8px;font-size:.875rem;font-weight:600" id="btn-save">
                        <i class="bi bi-save me-1"></i>Save Changes
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
    // Optional date fields (no constraints between them)
    flatpickr('#letter_issue_date',    { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y' });
    flatpickr('#letter_response_date', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y' });

    const fromPicker = flatpickr('#visit_from_date', {
        dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y',
        onChange: function (dates) { toPicker.set('minDate', dates[0] || null); }
    });
    const toPicker = flatpickr('#visit_to_date', {
        dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y',
    });

    $('#edit-visit-form').on('submit', function () {
        $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving…');
    });
})();
</script>
@endpush
