<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Action Matrix Admin') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sl-primary: #1b3a3a;
            --sl-bg: #f4f7f7;
            --sl-surface: #ffffff;
            --sl-text: #1b2525;
            --sl-muted: #748181;
            --sl-border: #e9eff1;
            --sl-radius: 12px;
            --sl-shadow: 0 10px 30px -5px rgba(27, 58, 58, 0.1), 0 4px 12px -2px rgba(27, 58, 58, 0.05);
        }

        body {
            min-height: 100vh;
            font-family: 'Public Sans', sans-serif;
            background-color: var(--sl-bg);
            color: var(--sl-text);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            background-image: 
                radial-gradient(at 0% 0%, rgba(27, 58, 58, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(27, 58, 58, 0.05) 0px, transparent 50%);
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            padding: 2.5rem;
            background: var(--sl-surface);
            border-radius: var(--sl-radius);
            box-shadow: var(--sl-shadow);
            border: 1px solid var(--sl-border);
        }

        .auth-logo {
            width: 60px;
            height: 60px;
            background: rgba(27, 58, 58, 0.08);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--sl-primary);
            font-size: 1.75rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--sl-text);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid var(--sl-border);
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--sl-primary);
            box-shadow: 0 0 0 4px rgba(27, 58, 58, 0.1);
        }

        .btn-sl-primary {
            background-color: var(--sl-primary);
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            border: none;
            transition: all 0.2s;
        }

        .btn-sl-primary:hover {
            background-color: #2a5a5a;
            transform: translateY(-1px);
        }

        .text-sl-muted { color: var(--sl-muted); }
        .text-sl-primary { color: var(--sl-primary); }
    </style>
</head>
<body>
    <div class="container auth-wrap d-flex align-items-center justify-content-center py-5">
        <div class="auth-card p-4 p-md-5">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
