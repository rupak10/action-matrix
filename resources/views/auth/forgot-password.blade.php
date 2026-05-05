<x-guest-layout>
    <div class="mb-4">
        <div class="text-uppercase small fw-bold text-secondary mb-2">Password recovery</div>
        <h2 class="h3 mb-2">Forgot your password?</h2>
        <p class="text-secondary mb-0">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2 d-block" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>{{ __('Email Password Reset Link') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
