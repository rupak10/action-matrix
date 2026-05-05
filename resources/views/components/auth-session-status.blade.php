@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success border-0 rounded-4']) }}>
        {{ $status }}
    </div>
@endif
