<x-guest-layout>
    <div class="text-center mb-5">
        <div class="auth-logo">
            <i class="bi bi-grid-fill"></i>
        </div>
        <h1 class="h4 fw-bold text-sl-primary mb-1">Welcome Back</h1>
        <p class="text-sl-muted small">Enter your credentials to access your account</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="emp_id" class="form-label">Employee ID</label>
            <div class="position-relative">
                <i class="bi bi-person-badge position-absolute top-50 start-0 translate-middle-y ms-3 text-sl-muted"></i>
                <x-text-input id="emp_id" class="ps-5" type="text" name="emp_id" :value="old('emp_id')" required autofocus autocomplete="username" placeholder="e.g. 105257" />
            </div>
            <x-input-error :messages="$errors->get('emp_id')" class="mt-2" />
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a class="smaller text-decoration-none text-sl-primary fw-600" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="position-relative">
                <i class="bi bi-lock position-absolute top-50 start-0 translate-middle-y ms-3 text-sl-muted"></i>
                <x-text-input id="password" class="ps-5" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="form-check mb-4">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label smaller text-sl-muted">Remember this device</label>
        </div>

        <div class="mb-4">
            <x-primary-button class="w-100 py-3">Sign In</x-primary-button>
        </div>

        <div class="text-center">
            <p class="smaller text-sl-muted mb-0">Don't have an account? <a href="#" class="text-sl-primary fw-bold text-decoration-none">Contact administrator</a></p>
        </div>
    </form>

    <style>
        .fw-600 { font-weight: 600; }
        .smaller { font-size: 0.8125rem; }
    </style>
</x-guest-layout>
