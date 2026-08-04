<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.po-info.index') }}" class="btn btn-sm btn-light border me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-bold text-sl-primary mb-0">Edit PO: {{ $po->po_code }}</h2>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="sl-card">
                <div class="sl-card-header">
                    <h5 class="mb-0">Update PO Details</h5>
                </div>
                <div class="p-4">
                    <form action="{{ route('admin.po-info.update', $po->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label for="po_code" class="form-label fw-bold">PO Code <span class="text-danger">*</span></label>
                                <input type="text" name="po_code" id="po_code"
                                       class="form-control @error('po_code') is-invalid @enderror"
                                       value="{{ old('po_code', $po->po_code) }}"
                                       maxlength="10" required>
                                @error('po_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="po_short_name" class="form-label fw-bold">Short Name</label>
                                <input type="text" name="po_short_name" id="po_short_name"
                                       class="form-control @error('po_short_name') is-invalid @enderror"
                                       value="{{ old('po_short_name', $po->po_short_name) }}"
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
                                       value="{{ old('po_name', $po->po_name) }}"
                                       maxlength="255" required>
                                @error('po_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 text-end pt-3">
                                <a href="{{ route('admin.po-info.index') }}" class="btn btn-light border px-4 me-2">Cancel</a>
                                <button type="submit" class="btn btn-sl-primary px-4">Update PO</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($po->created_by || $po->updated_by)
                <div class="mt-4 p-3 border rounded bg-white small text-sl-muted">
                    <div class="d-flex justify-content-between">
                        @if($po->created_by)
                            <span><strong>Created By:</strong> {{ $po->created_by }}
                                @if($po->created_at) ({{ \Carbon\Carbon::parse($po->created_at)->format('d M Y, h:i A') }}) @endif
                            </span>
                        @endif
                        @if($po->updated_by)
                            <span><strong>Last Updated:</strong> {{ $po->updated_by }}
                                @if($po->updated_at) ({{ \Carbon\Carbon::parse($po->updated_at)->format('d M Y, h:i A') }}) @endif
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .fw-bold { font-weight: 700; }
    </style>

</x-app-layout>
