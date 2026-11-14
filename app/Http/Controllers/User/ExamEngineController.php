<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Subtest;
use App\Models\UserAnswer;
use App\Models\UserAnswerOption;
use App\Services\ExamGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamEngineController extends Controller
{
    protected ExamGradingService $gradingService;

    public function __construct(ExamGradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

public function start(Subtest $subtest)
    {
        if (!$subtest->is_active || $subtest->questions()->count() === 0) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Subtest ini belum aktif atau belum memiliki soal yang siap dikerjakan.');
        }

        $userId = Auth::id();
        $session = ExamSession::where('user_id', $userId)
            ->where('subtest_id', $subtest->id)
            ->first();

        if ($session) {
            if ($session->status === 'finished') {
                return redirect()->route('user.exam.result', $subtest);
            }

if ($session->isExpired()) {
                $this->gradeSession($session);
                return redirect()->route('user.exam.result', $subtest)
                    ->with('info', 'Waktu ujian telah berakhir. Jawaban Anda telah otomatis dikumpulkan.');
            }

return redirect()->route('user.exam.board', $subtest);
        }

$now = now();
        $endsAt = $now->copy()->addMinutes($subtest->duration_minutes);

        $session = ExamSession::create([
            'user_id' => $userId,
            'subtest_id' => $subtest->id,
            'started_at' => $now,
            'ends_at' => $endsAt,
            'status' => 'ongoing',
            'tab_violation_count' => 0,
        ]);

        return redirect()->route('user.exam.board', $subtest);
    }

public function board(Subtest $subtest)
    {
        $userId = Auth::id();
        $session = ExamSession::where('user_id', $userId)
            ->where('subtest_id', $subtest->id)
            ->first();

        if (!$session) {
            return redirect()->route('user.exam.start', $subtest);
        }

        if ($session->status === 'finished') {
            return redirect()->route('user.exam.result', $subtest);
        }

if ($session->isExpired()) {
            $this->gradeSession($session);
            return redirect()->route('user.exam.result', $subtest)
                ->with('info', 'Waktu ujian telah berakhir. Jawaban Anda telah otomatis dikumpulkan.');
        }

$questions = $subtest->questions()
            ->with(['options' => function ($query) {
                $query->orderBy('label');
            }])
            ->orderBy('number')
            ->get();

$savedAnswers = $session->userAnswers()
            ->with(['options', 'userAnswerOptions'])
            ->get()
            ->keyBy('question_id')
            ->map(function ($ans) {
                return [
                    'option_ids' => $ans->options->pluck('id')->map(fn($id) => (int) $id)->all(),
                    'statement_values' => $ans->userAnswerOptions->pluck('value', 'option_id')->mapWithKeys(function ($val, $optId) {
                        return [(string) $optId => $val !== null ? (bool) $val : null];
                    })->all(),
                    'is_doubt' => (bool) $ans->is_doubt,
                ];
            });

$remainingSeconds = $session->remainingSeconds();
        $endsAtIso = $session->ends_at->toIso8601String();

        return view('user.exam.board', compact(
            'subtest',
            'session',
            'questions',
            'savedAnswers',
            'remainingSeconds',
            'endsAtIso'
        ));
    }

public function saveAnswer(Request $request, ExamSession $session)
    {
        
        if ($session->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized session.'], 403);
        }

if ($session->status !== 'ongoing') {
            return response()->json(['message' => 'Ujian telah selesai. Jawaban tidak dapat diubah.'], 400);
        }

if ($session->isExpired()) {
            $this->gradeSession($session);
            return response()->json([
                'expired' => true,
                'message' => 'Waktu pengerjaan telah habis.',
                'redirect' => route('user.exam.result', $session->subtest),
            ], 410);
        }

        $validated = $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['exists:options,id'],
            'option_id' => ['nullable', 'exists:options,id'],
            'value' => ['nullable', 'boolean'],
            'is_doubt' => ['nullable', 'boolean'],
        ]);

        $userAnswer = UserAnswer::updateOrCreate(
            [
                'exam_session_id' => $session->id,
                'question_id' => $validated['question_id'],
            ],
            [
                'is_doubt' => $request->boolean('is_doubt'),
            ]
        );

        $question = Question::find($validated['question_id']);

        if ($question && $question->isStatement()) {
            
            if ($request->filled('option_id') && $request->has('value')) {
                UserAnswerOption::updateOrCreate(
                    [
                        'user_answer_id' => $userAnswer->id,
                        'option_id' => $validated['option_id'],
                    ],
                    [
                        'value' => $request->boolean('value'),
                    ]
                );
            }
        } else {
            
            $optionIds = $request->input('option_ids', []);
            if ($request->filled('option_id') && empty($optionIds)) {
                $optionIds = [$request->input('option_id')];
            }

            $userAnswer->options()->sync($optionIds);
        }

        $userAnswer->load(['options', 'userAnswerOptions']);

        return response()->json([
            'success' => true,
            'question_id' => $userAnswer->question_id,
            'option_ids' => $userAnswer->options->pluck('id')->all(),
            'statement_values' => $userAnswer->userAnswerOptions->pluck('value', 'option_id')->all(),
            'is_doubt' => $userAnswer->is_doubt,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

public function recordViolation(ExamSession $session)
    {
        if ($session->user_id !== Auth::id() || $session->status !== 'ongoing') {
            return response()->json(['message' => 'Invalid session.'], 403);
        }

        $session->increment('tab_violation_count');

        return response()->json([
            'success' => true,
            'violations' => $session->tab_violation_count,
        ]);
    }

public function finish(ExamSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }

        if ($session->status === 'ongoing') {
            $this->gradeSession($session);
        }

        return redirect()->route('user.exam.result', $session->subtest)
            ->with('success', 'Ujian berhasil diselesaikan dan dinilai!');
    }

public function result(Subtest $subtest)
    {
        $userId = Auth::id();
        $session = ExamSession::where('user_id', $userId)
            ->where('subtest_id', $subtest->id)
            ->where('status', 'finished')
            ->first();

        if (!$session) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Sesi ujian belum diselesaikan.');
        }

$questions = $subtest->questions()
            ->with(['options' => function ($query) {
                $query->orderBy('label');
            }])
            ->orderBy('number')
            ->get();

        $savedAnswers = $session->userAnswers()
            ->with(['options', 'userAnswerOptions'])
            ->get()
            ->keyBy('question_id');

        $totalQuestions = $questions->count();
        $correctCount = $session->correct_count ?? 0;

$answeredCount = $savedAnswers->filter(function ($ans) {
            return $ans->userAnswerOptions->count() > 0 || $ans->options->count() > 0;
        })->count();

        $wrongCount = max(0, $answeredCount - $correctCount);
        $unansweredCount = max(0, $totalQuestions - $answeredCount);

        return view('user.exam.result', compact(
            'subtest',
            'session',
            'questions',
            'savedAnswers',
            'totalQuestions',
            'correctCount',
            'wrongCount',
            'unansweredCount'
        ));
    }

protected function gradeSession(ExamSession $session): void
    {
        $subtest = $session->subtest;
        $questions = $subtest->questions()->with('options')->get();
        $totalQuestions = $questions->count();

        $savedAnswers = $session->userAnswers()
            ->with(['options', 'userAnswerOptions'])
            ->get()
            ->keyBy('question_id');

        $correctCount = 0;
        foreach ($questions as $question) {
            $userAnswer = $savedAnswers->get($question->id);

            if ($this->gradingService->gradeUserAnswer($question, $userAnswer)) {
                $correctCount++;
            }
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;

        $session->update([
            'status' => 'finished',
            'finished_at' => now(),
            'score' => $score,
            'correct_count' => $correctCount,
        ]);
    }
}
