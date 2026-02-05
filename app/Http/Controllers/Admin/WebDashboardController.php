<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebDashboardController extends Controller
{
    /**
     * Show the dashboard
     */
    public function index()
    {
        $stats = [
            'total_apps' => App::count(),
            'active_apps' => App::where('is_active', true)->count(),
            'total_payments' => Payment::count(),
            'payments_by_status' => Payment::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status'),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'payments_today' => Payment::whereDate('created_at', today())->count(),
            'revenue_today' => Payment::where('status', 'paid')
                ->whereDate('paid_at', today())
                ->sum('amount'),
            'recent_payments' => Payment::with('app:id,name')
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
