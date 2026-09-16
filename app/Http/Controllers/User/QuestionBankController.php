<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    /**
     * Display question banks accessible to students.
     */
    public function index(Request $request)
    {
        $query = QuestionBank::active()
            ->withCount('items')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $banks = $query->paginate(9)->withQueryString();

        $categories = QuestionBank::active()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        return view('user.question_banks.index', compact('banks', 'categories'));
    }

    /**
     * Display interactive photo gallery of a question bank for students.
     */
    public function show(QuestionBank $questionBank)
    {
        // Guard: ensure bank is active
        if (!$questionBank->is_active) {
            abort(404, 'Bank soal tidak ditemukan atau belum dipublikasikan.');
        }

        $questionBank->load(['items' => function ($query) {
            $query->orderBy('order')->orderBy('id');
        }]);

        // Other recommended question banks
        $otherBanks = QuestionBank::active()
            ->where('id', '!=', $questionBank->id)
            ->withCount('items')
            ->latest()
            ->take(3)
            ->get();

        return view('user.question_banks.show', compact('questionBank', 'otherBanks'));
    }
}
