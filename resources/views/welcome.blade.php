<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Action Matrix Admin') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            min-height: 100vh;
            font-family: "Space Grotesk", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(111, 155, 118, 0.14), transparent 22rem),
                linear-gradient(160deg, #f3f8f4, #e5efe6 100%);
            color: #1f2b24;
        }

        .hero-card {
            border-radius: 2rem;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(43, 49, 55, 0.08);
            box-shadow: 0 24px 56px rgba(43, 49, 55, 0.08);
            backdrop-filter: blur(8px);
        }

        .hero-chip {
            background: rgba(63, 122, 82, 0.1);
            color: #3f7a52;
        }

        .hero-panel {
            background: linear-gradient(180deg, #284536, #1f3a2e);
        }

        .btn-success {
            background-color: #78644d;
            border-color: #78644d;
        }

        .btn-success:hover,
        .btn-success:focus,
        .btn-success:active {
            background-color: #346443;
            border-color: #346443;
        }

        .btn-outline-dark:hover {
            background-color: rgba(63, 122, 82, 0.08);
            border-color: rgba(43, 49, 55, 0.18);
            color: #1f2b24;
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="hero-card p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-7">
                            <div class="badge hero-chip rounded-pill px-3 py-2 mb-3">Laravel + Bootstrap + PostgreSQL</div>
                            <h1 class="display-5 fw-bold mb-3">Action Matrix Admin starter is ready</h1>
                            <p class="lead text-secondary mb-4">
                                This project now includes Breeze authentication, a custom Bootstrap admin layout, and PostgreSQL-first configuration for local development.
                            </p>
                            <div class="d-flex flex-wrap gap-3">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="btn btn-success btn-lg">Open Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-success btn-lg">Login</a>
                                    <a href="{{ route('register') }}" class="btn btn-outline-dark btn-lg">Register</a>
                                @endauth
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="hero-panel rounded-5 p-4 text-white">
                                <div class="small text-white-50 mb-2">Seeded admin</div>
                                <div class="fw-semibold mb-1">{{ env('ADMIN_EMAIL', 'admin@example.com') }}</div>
                                <div class="small text-white-50 mb-4">Password: {{ env('ADMIN_PASSWORD', 'password') }}</div>
                                <div class="small text-white-50 mb-2">Next recommended step</div>
                                <div class="fw-semibold">Convert `table-script-pg-v4.sql` into Laravel migrations.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
