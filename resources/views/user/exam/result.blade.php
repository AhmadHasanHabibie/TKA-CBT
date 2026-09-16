@extends('layouts.user')

@section('title', 'Hasil Ujian — ' . $subtest->name)

@section('content')
<div class="space-y-8" x-data="{ filter: 'all' }">
    <!-- Back to Dashboard Navigation -->
    <div>
        <a href="{{ route('user.dashboard') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-indigo-600 transition group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Dashboard Peserta
        </a>
    </div>

    <!-- Score & Performance Hero Card -->
    <div class="card p-7 sm:p-10 text-center max-w-2xl mx-auto">
        <span class="badge badge-emerald mx-auto mb-5 text-xs px-3 py-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Selesai Dinilai
        </span>

        <h1 class="text-xl font-black text-slate-900">Hasil Simulasi: {{ $subtest->name }}</h1>
        <p class="text-xs text-slate-500 mt-1.5">Diselesaikan pada {{ $session->finished_at ? $session->finished_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }}</p>

        <!-- Big Score Number -->
        <div class="my-8">
            <div class="inline-flex items-baseline gap-1">
                <span class="text-7xl font-black tracking-tight {{ $session->score >= 70 ? 'text-emerald-600' : ($session->score >= 50 ? 'text-indigo-600' : 'text-amber-500') }}">
                    {{ number_format($session->score, 2) }}
                </span>
                <span class="text-slate-400 font-bold text-xl">/ 100</span>
            </div>
        </div>

        <!-- Breakdown Statistics -->
        <div class="grid grid-cols-3 gap-3 p-5 rounded-2xl bg-slate-50 border border-slate-100">
            <div class="p-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Benar</span>
                <span class="text-3xl font-black text-slate-900">{{ $correctCount }}</span>
                <span class="block text-[10px] text-slate-400 mt-0.5">Soal</span>
            </div>
            <div class="p-2 border-x border-slate-200">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-rose-600 mb-1">Salah</span>
                <span class="text-3xl font-black text-slate-900">{{ $wrongCount }}</span>
                <span class="block text-[10px] text-slate-400 mt-0.5">Soal</span>
            </div>
            <div class="p-2">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kosong</span>
                <span class="text-3xl font-black text-slate-900">{{ $unansweredCount }}</span>
                <span class="block text-[10px] text-slate-400 mt-0.5">Soal</span>
            </div>
        </div>
    </div>

    <!-- Review Section Header & Filter Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-4 border-t border-slate-200">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Pembahasan &amp; Tinjauan Soal</h2>
            <p class="text-xs text-slate-500 mt-0.5">Evaluasi jawaban Anda dengan kunci jawaban dan pembahasan terstandar</p>
        </div>

        <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl w-fit">
            <button type="button" @click="filter = 'all'"
                class="px-3.5 py-2 rounded-lg text-xs font-bold transition"
                :class="filter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900'">
                Semua ({{ $totalQuestions }})
            </button>
            <button type="button" @click="filter = 'wrong'"
                class="px-3.5 py-2 rounded-lg text-xs font-bold transition"
                :class="filter === 'wrong' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-500 hover:text-rose-600'">
                Salah / Kosong ({{ $wrongCount + $unansweredCount }})
            </button>
        </div>
    </div>

    <!-- Questions Review Cards -->
    <div class="space-y-6">
        @foreach ($questions as $index => $q)
            @php
                $userAns = $savedAnswers->get($q->id);
                $isStatement = ($q->type === 'statement');
                
                if ($isStatement) {
                    $userStmtVals = [];
                    if ($userAns && $userAns->userAnswerOptions) {
                        foreach ($userAns->userAnswerOptions as $uao) {
                            $userStmtVals[$uao->option_id] = $uao->value;
                        }
                    }
                    $gradingService = new \App\Services\ExamGradingService();
                    $isCorrect = $gradingService->isStatementQuestionCorrect($q, $userStmtVals);
                    $isUnanswered = empty($userStmtVals);
                    $isWrong = !$isCorrect && !$isUnanswered;
                } else {
                    $userOptionIds = $userAns ? $userAns->options->pluck('id')->all() : [];
                    $correctOptionIds = $q->options->where('is_correct', true)->pluck('id')->all();
                    
                    sort($userOptionIds);
                    sort($correctOptionIds);
                    
                    $isCorrect = !empty($userOptionIds) && ($userOptionIds === $correctOptionIds);
                    $isUnanswered = empty($userOptionIds);
                    $isWrong = !$isCorrect && !$isUnanswered;
                }
            @endphp

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-5 transition"
                x-show="filter === 'all' || (filter === 'wrong' && {{ $isCorrect ? 'false' : 'true' }})"
                x-transition>

                <!-- Card Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl font-bold text-xs flex items-center justify-center font-mono {{ $isCorrect ? 'bg-emerald-500 text-white' : ($isWrong ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-600') }}">
                            #{{ $index + 1 }}
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-900">Soal Nomor {{ $index + 1 }}</span>
                                @if ($q->type === 'multiple')
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-[10px] font-extrabold text-indigo-700 uppercase tracking-wider">
                                        Pilihan Ganda Kompleks
                                    </span>
                                @elseif ($q->type === 'statement')
                                    <span class="px-2 py-0.5 rounded-md bg-teal-50 border border-teal-200 text-[10px] font-extrabold text-teal-700 uppercase tracking-wider">
                                        Sesuai / Tidak Sesuai
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($isCorrect)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Jawaban Benar</span>
                        </span>
                    @elseif ($isWrong)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span>Jawaban Salah</span>
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
                            Tidak Dijawab
                        </span>
                    @endif
                </div>

                <!-- Question Text / Stimulus -->
                @if ($isStatement)
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 text-sm leading-relaxed whitespace-pre-line font-normal">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Stimulus / Teks Bacaan</span>
                        </div>
                        <div>{{ $q->text }}</div>

                        @if ($q->image)
                            <div class="mt-4">
                                <img src="{{ asset('storage/' . $q->image) }}" alt="Diagram Stimulus"
                                    class="max-h-72 max-w-full rounded-xl border border-slate-200 object-contain bg-white p-1">
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-slate-900 text-sm font-medium leading-relaxed whitespace-pre-line">
                        {{ $q->text }}
                    </div>

                    @if ($q->image)
                        <div class="my-3">
                            <img src="{{ asset('storage/' . $q->image) }}" alt="Diagram Soal #{{ $index + 1 }}"
                                class="max-h-72 max-w-full rounded-xl border border-slate-200 object-contain bg-white p-1">
                        </div>
                    @endif
                @endif

                @if ($isStatement)
                    <!-- 3-Column Statement Evaluation Table -->
                    <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-2xs">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                                    <th class="py-3 px-4 w-7/12">Pernyataan</th>
                                    <th class="py-3 px-4 text-center w-2.5/12">Jawaban Kamu</th>
                                    <th class="py-3 px-4 text-center w-2.5/12">Kunci Jawaban</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($q->options as $opt)
                                    @php
                                        $val = $userStmtVals[$opt->id] ?? null;
                                        $expectedVal = (bool) $opt->is_correct;
                                        $hasAnswered = ($val !== null);
                                        $isMatch = $hasAnswered && ((bool) $val === $expectedVal);
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-start gap-2.5">
                                                <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 font-bold text-[11px] flex items-center justify-center flex-shrink-0 mt-0.5">
                                                    {{ $opt->label }}
                                                </span>
                                                <span class="text-slate-800 font-medium leading-relaxed">{{ $opt->text }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            @if (!$hasAnswered)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                                    Tidak Dijawab
                                                </span>
                                            @elseif ($isMatch)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                    <span>{{ (bool) $val ? 'Sesuai' : 'Tidak Sesuai' }}</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    <span>{{ (bool) $val ? 'Sesuai' : 'Tidak Sesuai' }}</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                {{ $expectedVal ? 'Sesuai' : 'Tidak Sesuai' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Options List with Status Highlighting -->
                    <div class="space-y-2 pt-2">
                        @foreach ($q->options as $opt)
                            @php
                                $isUserPick = in_array($opt->id, $userOptionIds);
                                $isTargetCorrect = (bool) $opt->is_correct;
                                
                                $isHit = $isUserPick && $isTargetCorrect;
                                $isMistake = $isUserPick && !$isTargetCorrect;
                                $isMissed = !$isUserPick && $isTargetCorrect;
                            @endphp

                            @if ($isHit)
                                <!-- Chosen and Correct -->
                                <div class="p-3.5 rounded-2xl border border-emerald-500 bg-emerald-50/50 text-emerald-950 font-bold text-xs flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center bg-emerald-600 text-white">
                                            {{ $opt->label }}
                                        </span>
                                        <span>{{ $opt->text }}</span>
                                    </div>
                                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-1 flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>{{ $q->type === 'multiple' ? 'Pilihan Anda — Kunci Benar' : 'Kunci Benar' }}</span>
                                    </span>
                                </div>
                            @elseif ($isMistake)
                                <!-- Chosen but Wrong -->
                                <div class="p-3.5 rounded-2xl border border-rose-300 bg-rose-50/50 text-rose-950 font-medium text-xs flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center bg-rose-500 text-white">
                                            {{ $opt->label }}
                                        </span>
                                        <span class="line-through text-rose-900/80">{{ $opt->text }}</span>
                                    </div>
                                    <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider flex items-center gap-1 flex-shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        <span>Pilihan Anda (Salah)</span>
                                    </span>
                                </div>
                            @elseif ($isMissed)
                                <!-- Omitted Correct Key -->
                                <div class="p-3.5 rounded-2xl border {{ $q->type === 'multiple' ? 'border-amber-400 bg-amber-50/60 text-amber-950 font-semibold' : 'border-emerald-500 bg-emerald-50/50 text-emerald-950 font-bold' }} text-xs flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center {{ $q->type === 'multiple' ? 'bg-amber-500 text-white' : 'bg-emerald-600 text-white' }}">
                                            {{ $opt->label }}
                                        </span>
                                        <span>{{ $opt->text }}</span>
                                    </div>
                                    <span class="text-[11px] font-bold {{ $q->type === 'multiple' ? 'text-amber-700' : 'text-emerald-700' }} uppercase tracking-wider flex items-center gap-1 flex-shrink-0">
                                        @if ($q->type === 'multiple')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            <span>Kunci Terlewat</span>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            <span>Kunci Benar</span>
                                        @endif
                                    </span>
                                </div>
                            @else
                                <!-- Unchosen Neutral Option -->
                                <div class="p-3.5 rounded-2xl border border-slate-200 bg-white text-slate-700 text-xs font-medium flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center bg-slate-100 text-slate-600">
                                            {{ $opt->label }}
                                        </span>
                                        <span>{{ $opt->text }}</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <!-- Explanation Box (PEMBAHASAN) -->
                @if ($q->explanation)
                    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100 text-xs leading-relaxed space-y-1">
                        <div class="flex items-center gap-1.5 text-indigo-700 font-bold uppercase tracking-wider text-[10px]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Pembahasan (PEMBAHASAN)</span>
                        </div>
                        <p class="text-slate-800 font-normal whitespace-pre-line">{{ $q->explanation }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
