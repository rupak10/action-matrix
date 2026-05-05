<x-guest-layout>
    <div class="mb-4">
        <div class="text-uppercase small fw-bold text-secondary mb-2">Security check</div>
        <h2 class="h3 mb-2">Confirm your password</h2>
        <p class="text-secondary mb-0">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 d-block" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>{{ __('Confirm') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
