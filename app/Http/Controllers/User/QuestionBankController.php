<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    /**
     * Display photo documentation albums accessible to students.
     */
    public function index(Request $request)
    {
        $query = QuestionBank::active()
            ->withCount('items')
            ->with(['items' => function ($q) {
                $q->orderBy('order')->orderBy('id');
            }])
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
     * Display interactive photo gallery of an album for students.
     */
    public function show(QuestionBank $questionBank)
    {
        if (!$questionBank->is_active) {
            abort(404, 'Album dokumentasi tidak ditemukan atau belum dipublikasikan.');
        }

        $questionBank->load(['items' => function ($query) {
            $query->orderBy('order')->orderBy('id');
        }]);

        $otherBanks = QuestionBank::active()
            ->where('id', '!=', $questionBank->id)
            ->withCount('items')
            ->latest()
            ->take(3)
            ->get();

        return view('user.question_banks.show', compact('questionBank', 'otherBanks'));
    }
}
