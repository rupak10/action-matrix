<div class="d-flex align-items-center gap-4">
    <div class="d-none d-xl-block">
        <h1 class="h5 mb-0 fw-bold text-sl-primary">Hello, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
        <p class="mb-0 text-sl-muted smaller">Here's what's happening with your projects today.</p>
    </div>

    <div class="search-box position-relative">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-sl-muted"></i>
        <input type="text" class="form-control border-sl-border bg-sl-bg ps-5 py-2 rounded-3 text-sm" placeholder="Search matrices, users, or projects..." style="min-width: 320px; font-size: 0.875rem;">
    </div>
</div>

<style>
    .smaller { font-size: 0.75rem; }
</style>
