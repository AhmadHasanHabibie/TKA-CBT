<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\QuestionBank;
use App\Models\QuestionBankItem;
use App\Models\Subtest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Student / User Photo Documentation Gallery Dashboard.
     */
    public function index()
    {
        $userId = Auth::id();

        $totalAlbums = 0;
        $totalPhotos = 0;
        $albums = collect();
        $recentPhotos = collect();

        try {
            $albums = QuestionBank::active()
                ->withCount('items')
                ->with(['items' => function ($q) {
                    $q->orderBy('order')->orderBy('id');
                }])
                ->orderByDesc('id')
                ->take(6)
                ->get();

            $totalAlbums = QuestionBank::active()->count();
            $totalPhotos = QuestionBankItem::whereHas('questionBank', function ($q) {
                $q->where('is_active', true);
            })->count();

            $recentPhotos = QuestionBankItem::with('questionBank')
                ->whereHas('questionBank', function ($q) {
                    $q->where('is_active', true);
                })
                ->latest('id')
                ->take(8)
                ->get();
        } catch (\Throwable $e) {
            // Fallback gracefully
        }

        // Keep legacy variables for backward-compatibility with any existing tests
        $subtests = Subtest::where('is_active', true)->withCount('questions')->orderBy('id')->get();
        $sessions = ExamSession::where('user_id', $userId)->get()->keyBy('subtest_id');
        $totalSubtests = $subtests->count();
        $completedSessions = $sessions->where('status', 'finished');
        $completedCount = $completedSessions->count();
        $averageScore = $completedCount > 0 ? $completedSessions->avg('score') : 0;

        return view('user.dashboard', compact(
            'albums',
            'recentPhotos',
            'totalAlbums',
            'totalPhotos',
            'subtests',
            'sessions',
            'totalSubtests',
            'completedCount',
            'averageScore'
        ));
    }
}
