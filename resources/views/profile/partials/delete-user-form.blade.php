<section>
    <header>
        <h2 class="h4 mb-2">{{ __('Delete Account') }}</h2>
        <p class="text-secondary mb-4">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <div class="alert alert-warning border-0 rounded-4">
        {{ __('Deleting your account is permanent. Enter your password and submit only if you want to remove your account.') }}
    </div>

    <form method="post" action="{{ route('profile.destroy') }}" class="row g-3">
        @csrf
        @method('delete')

        <div class="col-12">
            <x-input-label for="password" value="{{ __('Password') }}" />
            <x-text-input id="password" name="password" type="password" class="mt-2" placeholder="{{ __('Password') }}" />
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 d-block" />
        </div>

        <div class="col-12 d-flex align-items-center gap-2">
            <x-danger-button>{{ __('Delete Account') }}</x-danger-button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</section>
