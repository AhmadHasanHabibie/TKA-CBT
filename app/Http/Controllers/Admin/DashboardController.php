<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\LoginLog;
use App\Models\QuestionBank;
use App\Models\QuestionBankItem;
use App\Models\Subtest;
use App\Models\User;
use App\Services\LoginTrackerService;

class DashboardController extends Controller
{
    
    public function index(LoginTrackerService $loginTracker)
    {
        $loginTracker->ensureSchemaExists();

        $totalUsers = User::where('role', 'user')->count();
        $totalActiveSubtests = Subtest::where('is_active', true)->count();
        $totalAttempts = ExamSession::count();
        $globalAverageScore = ExamSession::where('status', 'finished')->avg('score') ?? 0;

        $totalAlbums = 0;
        $totalActiveAlbums = 0;
        $totalPhotos = 0;
        $latestAlbums = collect();

        try {
            $totalAlbums = QuestionBank::count();
            $totalActiveAlbums = QuestionBank::where('is_active', true)->count();
            $totalPhotos = QuestionBankItem::count();
            $latestAlbums = QuestionBank::withCount('items')
                ->latest()
                ->take(6)
                ->get();
        } catch (\Throwable $e) {
            
        }

        $recentAttempts = collect();
        $recentLogins = collect();
        $totalLogs = 0;

        try {
            $totalLogs = LoginLog::count();
            $recentLogins = LoginLog::with('user')
                ->latest('logged_in_at')
                ->take(10)
                ->get();
        } catch (\Throwable $e) {
            
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalActiveSubtests',
            'totalAttempts',
            'globalAverageScore',
            'recentAttempts',
            'totalAlbums',
            'totalActiveAlbums',
            'totalPhotos',
            'totalLogs',
            'latestAlbums',
            'recentLogins'
        ));
    }
}
