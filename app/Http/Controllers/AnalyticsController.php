<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    /**
     * Full-page analytics dashboard.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $user   = auth()->user();
        $period = $request->input('period', '6months');
        $poCode = $request->input('po_code', '');

        $data = $this->analytics->getAnalytics($user, $period, $poCode);

        return view('analytics.index', array_merge($data, [
            'selectedPeriod' => $period,
            'selectedPo'     => $poCode,
        ]));
    }

    /**
     * AJAX endpoint — returns JSON for filter-driven chart refresh.
     */
    public function getData(Request $request): \Illuminate\Http\JsonResponse
    {
        $user   = auth()->user();
        $period = $request->input('period', '6months');
        $poCode = $request->input('po_code', '');

        return response()->json(
            $this->analytics->getAnalytics($user, $period, $poCode)
        );
    }
}
