<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Follow up Action Matrix</title>
    <link rel="icon" type="image/png" href="{{ asset('pksf-logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --sl-primary: #1b3a3a;
            --sl-primary-soft: rgba(27, 58, 58, 0.08);
            --sl-bg: #f0f2f5;
            --sl-surface: #ffffff;
            --sl-text: #1b2525;
            --sl-muted: #748181;
            --sl-border: #e9eff1;
            --sl-border-strong: #dee6e9;
            --sl-success: #28a745;
            --sl-danger: #dc3545;
            --sl-warning: #ffc107;
            --sl-shadow: 0 4px 12px -2px rgba(27, 58, 58, 0.04), 0 2px 4px -1px rgba(27, 58, 58, 0.03);
            --sl-sidebar-width: 260px;
            --sl-header-height: 70px;
            --sl-radius: 12px;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--sl-bg);
            color: var(--sl-text);
            font-family: 'Public Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar-panel {
            width: var(--sl-sidebar-width);
            background: var(--sl-surface);
            border-right: 1px solid var(--sl-border);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1030;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-panel.is-collapsed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            height: var(--sl-header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--sl-border);
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem 1rem;
        }

        /* Content Shell */
        .content-shell {
            flex: 1;
            margin-left: var(--sl-sidebar-width);
            min-width: 0;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .content-shell.sidebar-collapsed {
            margin-left: 0;
        }

        /* Header Styles */
        .topbar-header {
            height: var(--sl-header-height);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--sl-border);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        /* Card Styles */
        .sl-card {
            background: var(--sl-surface);
            border: 1px solid var(--sl-border);
            border-radius: var(--sl-radius);
            box-shadow: var(--sl-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .sl-card:hover {
            box-shadow: 0 8px 16px -4px rgba(27, 58, 58, 0.08);
        }

        /* Typography & Utilities */
        .text-sl-muted { color: var(--sl-muted); }
        .text-sl-primary { color: var(--sl-primary); }
        .bg-sl-primary-soft { background-color: var(--sl-primary-soft); }

        .btn-sl-primary {
            background-color: var(--sl-primary);
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-sl-primary:hover {
            background-color: #2a5a5a;
            color: white;
            transform: translateY(-1px);
        }

        .badge-sl-soft {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 6px;
        }

        .badge-sl-soft-success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .badge-sl-soft-danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .badge-sl-soft-warning { background: rgba(255, 193, 7, 0.1); color: #9a6a0e; }

        @media (max-width: 991.98px) {
            .sidebar-panel { transform: translateX(-100%); }
            .sidebar-panel.is-open { transform: translateX(0); }
            .content-shell { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        <aside class="sidebar-panel" id="appSidebar">
            <div class="sidebar-header">
                <a href="{{ route('analytics.index') }}" class="d-flex align-items-center text-decoration-none">
                    <div class="bg-sl-primary-soft p-2 rounded-3 me-2">
                        <i class="bi bi-grid-fill text-sl-primary fs-4"></i>
                    </div>
                    <span class="fw-bold fs-5 text-sl-primary">Action Matrix</span>
                </a>
            </div>
            <div class="sidebar-content">
                @include('layouts.partials.sidebar')
            </div>
        </aside>

        <div class="content-shell" id="contentShell">
            <header class="topbar-header">
                <div class="container-fluid d-flex align-items-center justify-content-between px-0">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-link text-sl-primary p-0 me-3 d-lg-none" id="mobileToggle">
                            <i class="bi bi-list fs-3"></i>
                        </button>
                        <button class="btn btn-link text-sl-primary p-0 me-3 d-none d-lg-block" id="desktopToggle">
                            <i class="bi bi-list fs-3"></i>
                        </button>
                        <div class="d-none d-md-block">
                            @include('layouts.partials.header')
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-md-none">
                            <button class="btn btn-link text-sl-muted p-0">
                                <i class="bi bi-search fs-5"></i>
                            </button>
                        </div>
                        <button class="btn btn-link text-sl-muted p-0 position-relative">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                        </button>
                        <div class="vr mx-2 text-sl-border"></div>
                        <div class="dropdown">
                            @php
                                $authUser    = auth()->user();
                                $displayName = $authUser->name;
                                $displayRole = $authUser->roles->first()->name ?? $authUser->emp_type;
                                $avatarUrl   = 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=1b3a3a&color=fff';
                            @endphp
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                                <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="rounded-circle" width="32">
                                <div class="ms-2 d-none d-sm-block text-start">
                                    <p class="mb-0 fw-bold small text-sl-primary">{{ $displayName }}</p>
                                    <p class="mb-0 text-sl-muted smaller">{{ $displayRole }}</p>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end sl-card mt-2 border-0 shadow">
                                <li><a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
                                <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}"
                                           onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-grow-1 p-3 p-lg-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @isset($header)
                    <div class="mb-4">
                        {{ $header }}
                    </div>
                @endisset
                
                @if(isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>

            <footer class="p-3 p-lg-4 bg-white border-top">
                @include('layouts.partials.footer')
            </footer>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Select2 for any element with .sl-select2 class
            $('.sl-select2').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                    placeholder: $(this).data('placeholder'),
                });
            });
            const sidebar = document.getElementById('appSidebar');
            const content = document.getElementById('contentShell');
            const mobileToggle = document.getElementById('mobileToggle');
            const desktopToggle = document.getElementById('desktopToggle');

            // Handle Mobile Toggle
            mobileToggle?.addEventListener('click', () => {
                sidebar.classList.toggle('is-open');
            });

            // Handle Desktop Toggle
            desktopToggle?.addEventListener('click', () => {
                sidebar.classList.toggle('is-collapsed');
                content.classList.toggle('sidebar-collapsed');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 992 && 
                    !sidebar.contains(e.target) && 
                    !mobileToggle.contains(e.target) && 
                    sidebar.classList.contains('is-open')) {
                    sidebar.classList.remove('is-open');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
