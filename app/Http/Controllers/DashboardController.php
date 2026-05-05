<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        return view('dashboard', [
            'stats' => [
                [
                    'label' => 'Authenticated Area',
                    'value' => 'Online',
                    'help' => 'Laravel Breeze is protecting the admin routes.',
                ],
                [
                    'label' => 'UI Stack',
                    'value' => 'Bootstrap',
                    'help' => 'Custom sidebar, header, footer, and content shell are ready.',
                ],
                [
                    'label' => 'Database Target',
                    'value' => 'PostgreSQL',
                    'help' => 'Environment defaults are configured for a local PostgreSQL server.',
                ],
            ],
        ]);
    }
}
