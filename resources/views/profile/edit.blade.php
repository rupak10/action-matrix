<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="section-title mb-2">Account</div>
            <h2 class="h3 mb-1">{{ __('Profile Settings') }}</h2>
            <p class="page-muted mb-0">Manage your administrator information and account security.</p>
        </div>
    </x-slot>

    <div class="row g-4">
        <div class="col-12">
            <div class="content-card">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="col-lg-6">
            <div class="content-card h-100">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="col-lg-6">
            <div class="content-card h-100">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
