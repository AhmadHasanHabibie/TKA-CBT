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

            <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200 shadow-sm p-4 sm:p-6 md:p-8 space-y-4 sm:space-y-5 transition"
                x-show="filter === 'all' || (filter === 'wrong' && {{ $isCorrect ? 'false' : 'true' }})"
                x-transition>

                <!-- Card Header (Responsive, Never squished or gepeng) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div class="flex items-center justify-between w-full sm:w-auto gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 min-w-[2.25rem] min-h-[2.25rem] rounded-xl font-bold text-xs flex items-center justify-center font-mono shrink-0 aspect-square shadow-2xs {{ $isCorrect ? 'bg-emerald-500 text-white' : ($isWrong ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-600') }}">
                                #{{ $index + 1 }}
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm sm:text-base font-extrabold text-slate-900 leading-tight">Soal Nomor {{ $index + 1 }}</span>
                                </div>
                                @if ($q->type === 'multiple')
                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 text-[10px] font-extrabold text-indigo-700 tracking-wide uppercase">
                                        Pilihan Ganda Kompleks
                                    </span>
                                @elseif ($q->type === 'statement')
                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-md bg-teal-50 border border-teal-200 text-[10px] font-extrabold text-teal-700 tracking-wide uppercase">
                                        Sesuai / Tidak Sesuai
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Mobile result badge --}}
                        <div class="sm:hidden shrink-0">
                            @if ($isCorrect)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    <span>Benar</span>
                                </span>
                            @elseif ($isWrong)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                    <span>Salah</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                    Kosong
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Desktop result badge --}}
                    <div class="hidden sm:block shrink-0">
                        @if ($isCorrect)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span>Jawaban Benar</span>
                            </span>
                        @elseif ($isWrong)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                <span>Jawaban Salah</span>
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                Tidak Dijawab
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Question Text / Stimulus -->
                @if ($isStatement)
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 text-sm leading-relaxed whitespace-pre-line font-normal">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Stimulus / Teks Bacaan</span>
                        </div>
                        <div class="break-words">{{ $q->text }}</div>

                        @if ($q->image)
                            <div class="mt-4">
                                <img src="{{ asset('storage/' . $q->image) }}" alt="Diagram Stimulus"
                                    class="max-h-72 max-w-full rounded-xl border border-slate-200 object-contain bg-white p-1">
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-slate-900 text-sm sm:text-base font-medium leading-relaxed whitespace-pre-line break-words">
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
                    <!-- Desktop Statement Evaluation Table -->
                    <div class="hidden sm:block overflow-x-auto rounded-2xl border border-slate-200 shadow-2xs">
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
                                                <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
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
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                    <span>{{ (bool) $val ? 'Sesuai' : 'Tidak Sesuai' }}</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
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

                    <!-- Mobile Statement Cards (No awkward scrolling) -->
                    <div class="sm:hidden space-y-3">
                        @foreach ($q->options as $opt)
                            @php
                                $val = $userStmtVals[$opt->id] ?? null;
                                $expectedVal = (bool) $opt->is_correct;
                                $hasAnswered = ($val !== null);
                                $isMatch = $hasAnswered && ((bool) $val === $expectedVal);
                            @endphp
                            <div class="p-3.5 rounded-2xl border {{ $isMatch ? 'border-emerald-300 bg-emerald-50/40' : ($hasAnswered ? 'border-rose-200 bg-rose-50/40' : 'border-slate-200 bg-white') }} space-y-2.5">
                                <div class="flex items-start gap-2.5">
                                    <span class="w-6 h-6 min-w-[1.5rem] min-h-[1.5rem] rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 aspect-square mt-0.5">
                                        {{ $opt->label }}
                                    </span>
                                    <p class="text-xs font-medium text-slate-800 leading-relaxed flex-1 break-words">{{ $opt->text }}</p>
                                </div>
                                <div class="pt-2 border-t border-slate-200/60 flex flex-wrap items-center justify-between gap-1.5 text-[11px]">
                                    <div class="flex items-center gap-1 text-slate-500">
                                        <span>Jawaban Anda:</span>
                                        @if (!$hasAnswered)
                                            <span class="font-bold text-slate-500">Kosong</span>
                                        @elseif ($isMatch)
                                            <span class="font-bold text-emerald-700">✓ {{ (bool) $val ? 'Sesuai' : 'Tidak Sesuai' }}</span>
                                        @else
                                            <span class="font-bold text-rose-600">✕ {{ (bool) $val ? 'Sesuai' : 'Tidak Sesuai' }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1 text-slate-500">
                                        <span>Kunci:</span>
                                        <span class="font-bold text-emerald-700 bg-emerald-100/80 px-1.5 py-0.5 rounded">{{ $expectedVal ? 'Sesuai' : 'Tidak Sesuai' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Options List with Status Highlighting (Structured & Mobile Friendly) -->
                    <div class="space-y-3 pt-1">
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
                                <div class="rounded-2xl p-4 sm:p-5 border-2 border-emerald-500 bg-emerald-50/60 transition-all space-y-3 shadow-2xs">
                                    <div class="flex items-start gap-3 sm:gap-3.5">
                                        <span class="w-8 h-8 min-w-[2rem] min-h-[2rem] rounded-xl text-xs font-black flex items-center justify-center shrink-0 aspect-square bg-emerald-600 text-white shadow-xs mt-0.5">
                                            {{ $opt->label }}
                                        </span>
                                        <div class="flex-1 min-w-0 text-xs sm:text-sm font-semibold leading-relaxed pt-1 text-slate-900 break-words">
                                            {{ $opt->text }}
                                        </div>
                                    </div>
                                    <div class="pt-2.5 border-t border-emerald-200/80 flex flex-wrap items-center justify-between gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-[11px] sm:text-xs font-extrabold tracking-wide">
                                            <svg class="w-3.5 h-3.5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>{{ $q->type === 'multiple' ? 'Pilihan Anda — Kunci Benar' : 'Pilihan Anda Benar' }}</span>
                                        </span>
                                        <span class="text-[11px] sm:text-xs font-black text-emerald-700 uppercase tracking-wider">
                                            Benar ✓
                                        </span>
                                    </div>
                                </div>

                            @elseif ($isMistake)
                                <!-- Chosen but Wrong -->
                                <div class="rounded-2xl p-4 sm:p-5 border-2 border-rose-300 bg-rose-50/60 transition-all space-y-3 shadow-2xs">
                                    <div class="flex items-start gap-3 sm:gap-3.5">
                                        <span class="w-8 h-8 min-w-[2rem] min-h-[2rem] rounded-xl text-xs font-black flex items-center justify-center shrink-0 aspect-square bg-rose-500 text-white shadow-xs mt-0.5">
                                            {{ $opt->label }}
                                        </span>
                                        <div class="flex-1 min-w-0 text-xs sm:text-sm font-medium leading-relaxed pt-1 text-rose-950 break-words">
                                            <span class="line-through opacity-85">{{ $opt->text }}</span>
                                        </div>
                                    </div>
                                    <div class="pt-2.5 border-t border-rose-200/80 flex flex-wrap items-center justify-between gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 text-[11px] sm:text-xs font-extrabold tracking-wide">
                                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span>Pilihan Anda (Jawaban Salah)</span>
                                        </span>
                                        <span class="text-[11px] sm:text-xs font-black text-rose-600 uppercase tracking-wider">
                                            Salah ✕
                                        </span>
                                    </div>
                                </div>

                            @elseif ($isMissed)
                                <!-- Omitted Correct Key -->
                                <div class="rounded-2xl p-4 sm:p-5 border-2 {{ $q->type === 'multiple' ? 'border-amber-400 bg-amber-50/60' : 'border-emerald-400 bg-emerald-50/60' }} transition-all space-y-3 shadow-2xs">
                                    <div class="flex items-start gap-3 sm:gap-3.5">
                                        <span class="w-8 h-8 min-w-[2rem] min-h-[2rem] rounded-xl text-xs font-black flex items-center justify-center shrink-0 aspect-square {{ $q->type === 'multiple' ? 'bg-amber-500 text-white' : 'bg-emerald-600 text-white' }} shadow-xs mt-0.5">
                                            {{ $opt->label }}
                                        </span>
                                        <div class="flex-1 min-w-0 text-xs sm:text-sm font-semibold leading-relaxed pt-1 text-slate-900 break-words">
                                            {{ $opt->text }}
                                        </div>
                                    </div>
                                    <div class="pt-2.5 border-t {{ $q->type === 'multiple' ? 'border-amber-200/80' : 'border-emerald-200/80' }} flex flex-wrap items-center justify-between gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $q->type === 'multiple' ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-800' }} text-[11px] sm:text-xs font-extrabold tracking-wide">
                                            @if ($q->type === 'multiple')
                                                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <span>Kunci Jawaban Terlewat (Tidak Dipilih)</span>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>Kunci Jawaban Seharusnya</span>
                                            @endif
                                        </span>
                                        <span class="text-[11px] sm:text-xs font-black {{ $q->type === 'multiple' ? 'text-amber-700' : 'text-emerald-700' }} uppercase tracking-wider">
                                            Kunci Jawaban
                                        </span>
                                    </div>
                                </div>

                            @else
                                <!-- Unchosen Neutral Option -->
                                <div class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-white transition-all shadow-2xs">
                                    <div class="flex items-start gap-3 sm:gap-3.5">
                                        <span class="w-8 h-8 min-w-[2rem] min-h-[2rem] rounded-xl text-xs font-black flex items-center justify-center shrink-0 aspect-square bg-slate-100 text-slate-600 border border-slate-200 shadow-2xs mt-0.5">
                                            {{ $opt->label }}
                                        </span>
                                        <div class="flex-1 min-w-0 text-xs sm:text-sm font-medium leading-relaxed pt-1 text-slate-700 break-words">
                                            {{ $opt->text }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <!-- Explanation Box (PEMBAHASAN) -->
                @if ($q->explanation)
                    <div class="p-4 sm:p-5 rounded-2xl bg-indigo-50/70 border border-indigo-100/90 text-xs sm:text-sm leading-relaxed space-y-2">
                        <div class="flex items-center gap-2 text-indigo-800 font-bold uppercase tracking-wider text-xs">
                            <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <span>Kunci &amp; Pembahasan Soal</span>
                        </div>
                        <p class="text-slate-800 font-normal whitespace-pre-line leading-relaxed sm:pl-8 break-words">{{ $q->explanation }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
