<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\Subtest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Student / User CBT Dashboard.
     */
    public function index()
    {
        $userId = Auth::id();

        // Get all active subtests with questions count
        $subtests = Subtest::where('is_active', true)
            ->withCount('questions')
            ->orderBy('id')
            ->get();

        // Get student's existing sessions indexed by subtest_id
        $sessions = ExamSession::where('user_id', $userId)
            ->get()
            ->keyBy('subtest_id');

        // Calculate progress stats
        $totalSubtests = $subtests->count();
        $completedSessions = $sessions->where('status', 'finished');
        $completedCount = $completedSessions->count();
        $averageScore = $completedCount > 0 ? $completedSessions->avg('score') : 0;

        return view('user.dashboard', compact(
            'subtests',
            'sessions',
            'totalSubtests',
            'completedCount',
            'averageScore'
        ));
    }
}
