<x-guest-layout>
    <div class="mb-4">
        <div class="text-uppercase small fw-bold text-secondary mb-2">Email verification</div>
        <h2 class="h3 mb-2">Verify your address</h2>
        <p class="text-secondary mb-0">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you did not receive the email, we will gladly send you another.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success border-0 rounded-4">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('Resend Verification Email') }}</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link link-secondary p-0">{{ __('Log Out') }}</button>
        </form>
    </div>
</x-guest-layout>
