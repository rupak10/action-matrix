<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-sl-primary']) }}>
    {{ $slot }}
</button>
