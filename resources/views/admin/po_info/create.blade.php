<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.po-info.index') }}" class="btn btn-sm btn-light border me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-bold text-sl-primary mb-0">Add New PO</h2>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="sl-card">
                <div class="p-4">
                    <form action="{{ route('admin.po-info.store') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="po_code" class="form-label fw-bold">PO Code <span class="text-danger">*</span></label>
                                <input type="text" name="po_code" id="po_code"
                                       class="form-control @error('po_code') is-invalid @enderror"
                                       value="{{ old('po_code') }}"
                                       placeholder="e.g. 007"
                                       maxlength="10" required>
                                @error('po_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="po_short_name" class="form-label fw-bold">Short Name</label>
                                <input type="text" name="po_short_name" id="po_short_name"
                                       class="form-control @error('po_short_name') is-invalid @enderror"
                                       value="{{ old('po_short_name') }}"
                                       placeholder="e.g. BURO"
                                       maxlength="50">
                                @error('po_short_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                {{-- spacer --}}
                            </div>

                            <div class="col-12">
                                <label for="po_name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="po_name" id="po_name"
                                       class="form-control @error('po_name') is-invalid @enderror"
                                       value="{{ old('po_name') }}"
                                       placeholder="e.g. BURO Bangladesh"
                                       maxlength="255" required>
                                @error('po_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 text-end pt-3">
                                <a href="{{ route('admin.po-info.index') }}" class="btn btn-light border px-4 me-2">Cancel</a>
                                <button type="submit" class="btn btn-sl-primary px-4">Save PO</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fw-bold { font-weight: 700; }
    </style>

</x-app-layout>
