<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\Subtest;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display the Admin KPI Dashboard.
     */
    public function index()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalActiveSubtests = Subtest::where('is_active', true)->count();
        $totalAttempts = ExamSession::count();
        $globalAverageScore = ExamSession::where('status', 'finished')->avg('score') ?? 0;

        $recentAttempts = ExamSession::with(['user', 'subtest'])
            ->where('status', 'finished')
            ->latest('finished_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalActiveSubtests',
            'totalAttempts',
            'globalAverageScore',
            'recentAttempts'
        ));
    }
}
