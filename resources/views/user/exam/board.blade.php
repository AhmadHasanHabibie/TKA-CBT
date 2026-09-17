<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Papan Ujian — {{ $subtest->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800 select-none antialiased overflow-x-hidden"
    oncontextmenu="return false;"
    x-data="examEngine({
        sessionId: {{ $session->id }},
        subtestSlug: '{{ $subtest->slug }}',
        endsAtTimestamp: {{ $session->ends_at->timestamp * 1000 }},
        questions: {{ Js::from($questions) }},
        initialAnswers: {{ Js::from($savedAnswers) }},
        saveUrl: '{{ route('user.exam.save-answer', $session) }}',
        violationUrl: '{{ route('user.exam.violation', $session) }}',
        finishUrl: '{{ route('user.exam.finish', $session) }}'
    })"
    x-init="initEngine()"
    x-cloak>

    {{-- ── Top Floating Responsive Header ──────────────────────── --}}
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-40 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-18 flex items-center justify-between gap-3 sm:gap-4">
            
            {{-- Title & Brand --}}
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-xs shadow-xs flex-shrink-0">
                    T
                </span>
                <div class="min-w-0">
                    <h1 class="font-bold text-slate-900 text-xs sm:text-sm leading-tight truncate">{{ $subtest->name }}</h1>
                    <span class="hidden sm:block text-[11px] text-slate-400 font-medium truncate">Tes Kompetensi Akademik (TKA)</span>
                </div>
            </div>

            {{-- Controls: Mobile Burger Button, Timer, Auto-save Pill, Finish Button --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                
                {{-- Server-Authoritative Floating Countdown Timer --}}
                <div class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1.5 rounded-xl border text-xs font-mono font-bold tracking-wider shadow-xs transition"
                    :class="remainingSeconds < 300 ? 'bg-rose-600 border-rose-500 text-white animate-pulse' : 'bg-slate-900 border-slate-800 text-white'">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="timerDisplay">--:--:--</span>
                </div>

                {{-- Auto-Save Status Pill (Desktop/Tablet) --}}
                <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60">
                    <span class="w-2 h-2 rounded-full" :class="saving ? 'bg-amber-400 animate-ping' : 'bg-emerald-500'"></span>
                    <span x-text="saving ? 'Menyimpan...' : 'Tersimpan'">Tersimpan</span>
                </div>

                {{-- TOP BURGER NAV BUTTON (Navigasi Soal di Bagian Atas untuk HP & Laptop) --}}
                <button type="button" @click="mobileNavOpen = true"
                    class="inline-flex items-center gap-2 px-2.5 sm:px-3.5 py-1.5 rounded-xl border border-indigo-200/90 bg-indigo-50/90 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition active:scale-95 shadow-2xs"
                    title="Buka Navigasi Soal">
                    <div class="flex flex-col justify-center items-center w-4 h-4 gap-[3px]">
                        <span class="block w-4 h-[2px] bg-indigo-600 rounded-full"></span>
                        <span class="block w-4 h-[2px] bg-indigo-600 rounded-full"></span>
                        <span class="block w-4 h-[2px] bg-indigo-600 rounded-full"></span>
                    </div>
                    <span class="hidden sm:inline">Navigasi</span>
                    <span class="font-mono text-indigo-950 font-extrabold bg-indigo-200/70 px-1.5 py-0.5 rounded-md text-[11px]" x-text="(currentIndex + 1) + '/' + questions.length"></span>
                </button>

                {{-- Finish Button --}}
                <button type="button" @click="confirmFinishModal = true"
                    class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition active:scale-95 whitespace-nowrap">
                    <span>Selesaikan</span>
                </button>
            </div>
        </div>
    </header>

    {{-- ── Main Exam Board Container ───────────────────────────── --}}
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-7 pb-12 sm:pt-9 sm:pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 lg:gap-8 items-start">
            
            {{-- Left: Main Question Area (col-span-3) --}}
            <div class="lg:col-span-3 space-y-4 sm:space-y-6">
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-sm sm:shadow-md p-5 sm:p-8 md:p-9 transition-all">
                    
                    {{-- Question Header: Number indicator & Badges --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-5 sm:mb-6 border-b border-slate-100">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 rounded-xl bg-slate-900 text-white text-xs font-bold font-mono shadow-xs">
                                Soal <span x-text="currentIndex + 1"></span> dari <span x-text="questions.length"></span>
                            </span>

                            <template x-if="currentQuestion.type === 'multiple'">
                                <span class="px-2.5 py-1 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 text-[11px] font-extrabold tracking-wide">
                                    Pilihan Ganda Kompleks
                                </span>
                            </template>

                            <template x-if="currentQuestion.type === 'statement'">
                                <span class="px-2.5 py-1 rounded-xl bg-teal-50 border border-teal-200 text-teal-700 text-[11px] font-extrabold tracking-wide">
                                    Sesuai / Tidak Sesuai
                                </span>
                            </template>

                            <template x-if="currentAnswer.is_doubt">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Ragu-ragu</span>
                                </span>
                            </template>
                        </div>

                        {{-- Doubt Toggle Button --}}
                        <button type="button" @click="toggleDoubt()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold transition active:scale-95 shadow-2xs"
                            :class="currentAnswer.is_doubt ? 'bg-amber-500 border-amber-600 text-white' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="currentAnswer.is_doubt ? 'Hapus Ragu-ragu' : 'Tandai Ragu-ragu'">Tandai Ragu-ragu</span>
                        </button>
                    </div>

                    {{-- Statement Type: Stimulus Reading Container & Notice --}}
                    <template x-if="currentQuestion.type === 'statement'">
                        <div>
                            <div class="mb-5 p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 text-sm leading-relaxed whitespace-pre-line font-normal shadow-2xs">
                                <div class="flex items-center gap-2 mb-2.5 pb-2 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Stimulus / Teks Bacaan</span>
                                </div>
                                <div x-text="currentQuestion.text" class="break-words"></div>

                                {{-- Stimulus Image Attachment if exists --}}
                                <template x-if="currentQuestion.image">
                                    <div class="mt-4 flex flex-col items-center sm:items-start">
                                        <div class="relative group cursor-pointer inline-block rounded-2xl overflow-hidden border border-slate-200 bg-white p-2 shadow-2xs hover:shadow-md transition"
                                            @click="openImageModal('/storage/' + currentQuestion.image)">
                                            <img :src="'/storage/' + currentQuestion.image" alt="Diagram Stimulus"
                                                class="max-h-72 max-w-full rounded-xl object-contain">
                                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg bg-slate-900/80 text-white text-[11px] font-bold backdrop-blur-xs flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                                <span>Klik Perbesar</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mb-4 flex items-center gap-2.5 p-3.5 rounded-2xl bg-teal-50/90 border border-teal-200 text-teal-900 text-xs font-semibold leading-normal">
                                <svg class="w-4 h-4 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Tentukan apakah setiap pernyataan berikut <strong>Sesuai</strong> atau <strong>Tidak Sesuai</strong> berdasarkan stimulus di atas.</span>
                            </div>

                            {{-- Statements List with Dual Toggles --}}
                            <div class="space-y-3">
                                <template x-for="option in currentQuestion.options" :key="option.id">
                                    <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-slate-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 shadow-2xs">
                                        <div class="flex items-start gap-3 flex-1 min-w-0">
                                            <div class="w-7 h-7 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5"
                                                x-text="option.label">
                                            </div>
                                            <p class="text-sm font-medium text-slate-800 leading-relaxed break-words" x-text="option.text"></p>
                                        </div>
                                        <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0 pt-1 sm:pt-0">
                                            {{-- Sesuai Button --}}
                                            <button type="button"
                                                @click="setStatementValue(option.id, true)"
                                                class="px-3.5 sm:px-4 py-2 rounded-xl text-xs font-bold border transition flex items-center gap-1.5 active:scale-95"
                                                :class="getStatementValue(option.id) === true ? 'bg-emerald-600 border-emerald-600 text-white shadow-xs' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>Sesuai</span>
                                            </button>

                                            {{-- Tidak Sesuai Button --}}
                                            <button type="button"
                                                @click="setStatementValue(option.id, false)"
                                                class="px-3.5 sm:px-4 py-2 rounded-xl text-xs font-bold border transition flex items-center gap-1.5 active:scale-95"
                                                :class="getStatementValue(option.id) === false ? 'bg-rose-600 border-rose-600 text-white shadow-xs' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                <span>Tidak Sesuai</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Standard (Single & Multiple PGK) Type --}}
                    <template x-if="currentQuestion.type !== 'statement'">
                        <div>
                            {{-- Question Content Text --}}
                            <div class="prose prose-slate max-w-none text-sm sm:text-base text-slate-900 leading-relaxed font-medium mb-4 whitespace-pre-line break-words"
                                x-text="currentQuestion.text">
                            </div>

                            {{-- Question Image Attachment if exists --}}
                            <template x-if="currentQuestion.image">
                                <div class="mb-5 flex flex-col items-center sm:items-start">
                                    <div class="relative group cursor-pointer inline-block rounded-2xl overflow-hidden border border-slate-200 bg-white p-2 shadow-2xs hover:shadow-md transition"
                                        @click="openImageModal('/storage/' + currentQuestion.image)">
                                        <img :src="'/storage/' + currentQuestion.image" alt="Diagram Soal"
                                            class="max-h-80 max-w-full rounded-xl object-contain">
                                        <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg bg-slate-900/80 text-white text-[11px] font-bold backdrop-blur-xs flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                            <span>Klik Perbesar</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- PGK Multiple Choice Notice Banner --}}
                            <template x-if="currentQuestion.type === 'multiple'">
                                <div class="mb-4 flex items-center gap-2.5 p-3.5 rounded-2xl bg-indigo-50/90 border border-indigo-200 text-indigo-900 text-xs font-semibold leading-normal">
                                    <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Pilihan Ganda Kompleks — Jawaban benar bisa lebih dari satu. Pilih semua opsi yang menurut Anda benar.</span>
                                </div>
                            </template>

                            {{-- Options Cards (Single Radio vs Multi Checkbox) --}}
                            <div class="space-y-3">
                                <template x-for="option in currentQuestion.options" :key="option.id">
                                    <label class="flex items-start gap-3 sm:gap-3.5 p-3.5 sm:p-4 rounded-2xl border-2 transition cursor-pointer group select-none shadow-2xs"
                                        :class="isOptionSelected(option.id) ? 'border-indigo-600 bg-indigo-50/50 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'">
                                        
                                        <template x-if="currentQuestion.type === 'multiple'">
                                            <input type="checkbox" :name="'question_' + currentQuestion.id + '[]'" :value="option.id"
                                                :checked="isOptionSelected(option.id)"
                                                @change="toggleOption(option.id)"
                                                class="hidden">
                                        </template>
                                        <template x-if="currentQuestion.type !== 'multiple'">
                                            <input type="radio" :name="'question_' + currentQuestion.id" :value="option.id"
                                                :checked="isOptionSelected(option.id)"
                                                @change="selectSingleOption(option.id)"
                                                class="hidden">
                                        </template>

                                        {{-- Multi-select Checkbox Square Indicator --}}
                                        <template x-if="currentQuestion.type === 'multiple'">
                                            <div class="w-5 h-5 mt-1 rounded-md border flex items-center justify-center flex-shrink-0 transition"
                                                :class="isOptionSelected(option.id) ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-300 bg-white group-hover:border-slate-400'">
                                                <svg x-show="isOptionSelected(option.id)" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </template>
                                        
                                        {{-- Option Label Pill (A, B, C, D, E) --}}
                                        <div class="w-8 h-8 rounded-xl font-bold text-xs flex items-center justify-center flex-shrink-0 transition"
                                            :class="isOptionSelected(option.id) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'"
                                            x-text="option.label">
                                        </div>

                                        {{-- Option Text Content --}}
                                        <div class="text-sm text-slate-800 pt-1 font-medium leading-relaxed flex-1 break-words"
                                            x-text="option.text">
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Bottom Navigation Action Buttons (Clean Left & Right Only) --}}
                <div class="flex items-center justify-between pt-4 sm:pt-6 gap-3">
                    <button type="button" @click="prevQuestion()" :disabled="currentIndex === 0"
                        class="inline-flex items-center gap-1.5 sm:gap-2 px-4 sm:px-6 py-2.5 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed transition shadow-2xs active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                        <span><span class="hidden sm:inline">Soal </span>Sebelumnya</span>
                    </button>

                    <template x-if="currentIndex < questions.length - 1">
                        <button type="button" @click="nextQuestion()"
                            class="inline-flex items-center gap-1.5 sm:gap-2 px-5 sm:px-7 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-bold hover:bg-indigo-600 transition shadow-xs active:scale-95">
                            <span><span class="hidden sm:inline">Soal </span>Selanjutnya</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </template>

                    <template x-if="currentIndex === questions.length - 1">
                        <button type="button" @click="confirmFinishModal = true"
                            class="inline-flex items-center gap-1.5 sm:gap-2 px-5 sm:px-7 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold transition shadow-xs active:scale-95">
                            <span>Selesaikan Ujian</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Right: Question Number Grid Desktop Sidebar (lg:col-span-1) --}}
            <div class="hidden lg:block lg:col-span-1 sticky top-20">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
                    
                    {{-- Sidebar Header --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">Navigasi Soal</h2>
                            <span class="text-xs font-bold text-indigo-600 font-mono">
                                <span x-text="answeredCount"></span>/<span x-text="questions.length"></span>
                            </span>
                        </div>
                        {{-- Clean Progress Bar --}}
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-indigo-600 h-full rounded-full transition-all duration-300"
                                :style="'width: ' + ((answeredCount / Math.max(questions.length, 1)) * 100) + '%'"></div>
                        </div>
                    </div>

                    {{-- Legend Grid --}}
                    <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-500 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 flex-shrink-0"></span>
                            <span>Lengkap</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500 flex-shrink-0"></span>
                            <span>Sebagian</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                            <span>Ragu-ragu</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-300 flex-shrink-0"></span>
                            <span>Belum</span>
                        </div>
                    </div>

                    {{-- Grid Numbers Container (Clean, Rounded-2xl, Zero Black Artifacts) --}}
                    <div class="p-1 max-h-72 overflow-y-auto pr-1">
                        <div class="grid grid-cols-5 gap-2.5">
                            <template x-for="(q, idx) in questions" :key="q.id">
                                <button type="button" @click="goToQuestion(idx)"
                                    class="h-11 w-full rounded-2xl text-xs font-bold flex flex-col items-center justify-center transition-all duration-150 focus:outline-none relative active:scale-95"
                                    :class="getGridClass(q.id, idx)">
                                    <span x-text="idx + 1" class="leading-none"></span>
                                    <template x-if="currentIndex === idx">
                                        <span class="w-1.5 h-1.5 rounded-full mt-1 transition-all"
                                            :class="isQuestionAnsweredFully(q) || isQuestionAnsweredPartially(q) || (answers[q.id] && answers[q.id].is_doubt) ? 'bg-white' : 'bg-indigo-600'"></span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Finish CTA Button --}}
                    <div class="pt-2 border-t border-slate-100">
                        <button type="button" @click="confirmFinishModal = true"
                            class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition active:scale-95">
                            Kumpulkan Jawaban
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    {{-- ── Navigation Slide-Over Drawer (Smooth Slide-In From Right for All Viewports) ── --}}
    <div x-show="mobileNavOpen" style="display: none;"
        class="fixed inset-0 z-50 overflow-hidden"
        aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        
        {{-- Backdrop with smooth fade --}}
        <div x-show="mobileNavOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
            @click="mobileNavOpen = false"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10 z-50">
            {{-- Slide-Over Panel with smooth slide-in from right --}}
            <div x-show="mobileNavOpen"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-xs sm:max-w-sm bg-white shadow-2xl flex flex-col overflow-hidden">
                
                {{-- Drawer Top Bar --}}
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                            <div class="flex flex-col justify-center items-center w-3.5 h-3.5 gap-[2px]">
                                <span class="block w-3.5 h-[1.5px] bg-white rounded-full"></span>
                                <span class="block w-3.5 h-[1.5px] bg-white rounded-full"></span>
                                <span class="block w-3.5 h-[1.5px] bg-white rounded-full"></span>
                            </div>
                        </div>
                        <div>
                            <h3 id="slide-over-title" class="font-extrabold text-slate-900 text-sm">Navigasi Soal</h3>
                            <p class="text-[11px] text-slate-500 font-medium">
                                <span class="font-bold text-indigo-600 font-mono" x-text="answeredCount"></span> dari <span class="font-bold text-slate-700 font-mono" x-text="questions.length"></span> terjawab
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="mobileNavOpen = false"
                        class="w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition active:scale-95 shadow-2xs"
                        aria-label="Tutup">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Progress Bar --}}
                <div class="px-4 sm:px-5 pt-3">
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-indigo-600 h-full rounded-full transition-all duration-300"
                            :style="'width: ' + ((answeredCount / Math.max(questions.length, 1)) * 100) + '%'"></div>
                    </div>
                </div>

                {{-- Legend Grid --}}
                <div class="px-4 sm:px-5 py-3 border-b border-slate-100">
                    <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600 font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 flex-shrink-0"></span>
                            <span>Lengkap</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500 flex-shrink-0"></span>
                            <span>Sebagian</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                            <span>Ragu-ragu</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-300 flex-shrink-0"></span>
                            <span>Belum</span>
                        </div>
                    </div>
                </div>

                {{-- Question Numbers Grid (Smooth Scroll, Rounded-2xl, Zero Black Artifacts) --}}
                <div class="flex-1 overflow-y-auto p-4 sm:p-5">
                    <div class="grid grid-cols-5 gap-2.5">
                        <template x-for="(q, idx) in questions" :key="q.id">
                            <button type="button" @click="goToQuestion(idx); mobileNavOpen = false;"
                                class="h-11 w-full rounded-2xl text-xs font-bold flex flex-col items-center justify-center transition-all duration-150 focus:outline-none relative active:scale-95"
                                :class="getGridClass(q.id, idx)">
                                <span x-text="idx + 1" class="leading-none"></span>
                                <template x-if="currentIndex === idx">
                                    <span class="w-1.5 h-1.5 rounded-full mt-1 transition-all"
                                        :class="isQuestionAnsweredFully(q) || isQuestionAnsweredPartially(q) || (answers[q.id] && answers[q.id].is_doubt) ? 'bg-white' : 'bg-indigo-600'"></span>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Drawer Footer CTA --}}
                <div class="p-4 border-t border-slate-100 bg-slate-50/70">
                    <button type="button" @click="confirmFinishModal = true; mobileNavOpen = false;"
                        class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition active:scale-95">
                        Kumpulkan Jawaban
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Anti-Cheat Violation Toast Notification --}}
    <div x-show="violationToast" style="display: none;"
        class="fixed bottom-5 right-5 z-50 p-4 rounded-2xl bg-rose-600 text-white text-xs font-semibold shadow-xl flex items-center gap-3 border border-rose-500 max-w-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2">
        <svg class="w-5 h-5 flex-shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="font-bold">Peringatan Integritas Ujian!</p>
            <p class="text-[11px] text-rose-100">Anda terdeteksi meninggalkan jendela ujian (<span x-text="violations"></span> kali).</p>
        </div>
    </div>

    {{-- Confirm Submit Modal --}}
    <div x-show="confirmFinishModal" style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 text-center"
            @click.away="confirmFinishModal = false">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h3 class="text-lg font-bold text-slate-900">Konfirmasi Selesai Ujian</h3>
            <p class="text-xs text-slate-500 mt-1">
                Apakah Anda yakin ingin menyelesaikan simulasi subtest <span class="font-bold text-slate-700">{{ $subtest->name }}</span>?
            </p>

            {{-- Summary Chips --}}
            <div class="my-5 p-4 rounded-2xl bg-slate-50 border border-slate-100 grid grid-cols-3 gap-2 text-xs">
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Terjawab</span>
                    <span class="text-base font-extrabold text-indigo-600" x-text="answeredCount">0</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Ragu-ragu</span>
                    <span class="text-base font-extrabold text-amber-500" x-text="doubtCount">0</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Kosong</span>
                    <span class="text-base font-extrabold text-rose-500" x-text="questions.length - answeredCount">0</span>
                </div>
            </div>

            <form method="POST" :action="finishUrl" class="space-y-3">
                @csrf
                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition active:scale-95">
                    Ya, Selesaikan & Lihat Skor
                </button>
                <button type="button" @click="confirmFinishModal = false"
                    class="w-full py-2.5 px-4 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition active:scale-95">
                    Kembali Mengerjakan
                </button>
            </form>
        </div>
    </div>

    {{-- Time's Up Modal --}}
    <div x-show="timeUpModal" style="display: none;"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-center animate-bounce-short">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-extrabold text-slate-900">Waktu Ujian Telah Berakhir!</h3>
            <p class="text-xs sm:text-sm text-slate-500 mt-2">
                Durasi pengerjaan subtest telah selesai. Seluruh jawaban Anda telah disimpan otomatis dan sedang dialihkan ke halaman evaluasi.
            </p>
            <div class="mt-6 flex items-center justify-center gap-2 text-indigo-600 font-bold text-xs">
                <svg class="animate-spin h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Mengalihkan ke halaman hasil...</span>
            </div>
        </div>
    </div>

    {{-- Image Lightbox Modal --}}
    <div x-show="showImageModal" style="display: none;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm"
        @keydown.escape.window="showImageModal = false"
        @click.self="showImageModal = false">
        <div class="relative max-w-5xl max-h-[90vh] bg-white rounded-3xl p-3 shadow-2xl overflow-hidden flex flex-col items-center">
            <button type="button" @click="showImageModal = false"
                class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-slate-900/70 text-white flex items-center justify-center hover:bg-slate-900 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <img :src="modalImageSrc" alt="Perbesar Gambar Soal" class="max-h-[82vh] max-w-full rounded-2xl object-contain">
        </div>
    </div>

    {{-- Alpine.js Exam Engine Component Logic --}}
    <script>
        function examEngine(config) {
            return {
                sessionId: config.sessionId,
                subtestSlug: config.subtestSlug,
                endsAt: config.endsAtTimestamp,
                questions: config.questions || [],
                answers: config.initialAnswers || {},
                saveUrl: config.saveUrl,
                violationUrl: config.violationUrl,
                finishUrl: config.finishUrl,

                currentIndex: 0,
                remainingSeconds: 0,
                timerDisplay: '--:--:--',
                timerInterval: null,
                saving: false,
                confirmFinishModal: false,
                timeUpModal: false,
                showImageModal: false,
                modalImageSrc: '',
                violationToast: false,
                violations: 0,
                mobileNavOpen: false,

                openImageModal(src) {
                    this.modalImageSrc = src;
                    this.showImageModal = true;
                },

                get currentQuestion() {
                    return this.questions[this.currentIndex] || { options: [] };
                },

                get currentAnswer() {
                    const ans = this.answers[this.currentQuestion.id];
                    if (!ans) return { option_ids: [], statement_values: {}, is_doubt: false };
                    const ids = ans.option_ids || (ans.option_id ? [ans.option_id] : []);
                    const stmtVals = ans.statement_values || {};
                    return { option_ids: ids, statement_values: stmtVals, is_doubt: !!ans.is_doubt };
                },

                isOptionSelected(optId) {
                    const ids = this.currentAnswer.option_ids || [];
                    return ids.includes(optId);
                },

                getStatementValue(optId) {
                    const stmtVals = this.currentAnswer.statement_values || {};
                    const val = stmtVals[optId] !== undefined ? stmtVals[optId] : stmtVals[String(optId)];
                    return val !== undefined && val !== null ? val : null;
                },

                setStatementValue(optionId, val) {
                    const qId = this.currentQuestion.id;
                    const isDoubt = this.currentAnswer.is_doubt;
                    const currentVals = { ...(this.currentAnswer.statement_values || {}) };

                    currentVals[optionId] = val;
                    currentVals[String(optionId)] = val;

                    if (!this.answers[qId]) {
                        this.answers[qId] = { option_ids: [], statement_values: {}, is_doubt: false };
                    }
                    this.answers[qId].statement_values = currentVals;
                    this.answers[qId].is_doubt = isDoubt;

                    this.sendStatementAjax(qId, optionId, val, isDoubt);
                },

                isQuestionAnsweredFully(q) {
                    if (!q) return false;
                    const ans = this.answers[q.id];
                    if (!ans) return false;
                    if (q.type === 'statement') {
                        const vals = ans.statement_values || {};
                        const totalOpts = (q.options || []).length;
                        if (totalOpts === 0) return false;
                        let answered = 0;
                        for (const opt of q.options) {
                            const v = vals[opt.id] !== undefined ? vals[opt.id] : vals[String(opt.id)];
                            if (v !== undefined && v !== null) {
                                answered++;
                            }
                        }
                        return answered === totalOpts;
                    }

                    const optIds = ans.option_ids || (ans.option_id ? [ans.option_id] : []);
                    return optIds.length > 0;
                },

                isQuestionAnsweredPartially(q) {
                    if (!q || q.type !== 'statement') return false;
                    const ans = this.answers[q.id];
                    if (!ans) return false;
                    const vals = ans.statement_values || {};
                    const totalOpts = (q.options || []).length;
                    if (totalOpts === 0) return false;
                    let answered = 0;
                    for (const opt of q.options) {
                        const v = vals[opt.id] !== undefined ? vals[opt.id] : vals[String(opt.id)];
                        if (v !== undefined && v !== null) {
                            answered++;
                        }
                    }
                    return answered > 0 && answered < totalOpts;
                },

                get answeredCount() {
                    return this.questions.filter(q => this.isQuestionAnsweredFully(q)).length;
                },

                get doubtCount() {
                    return Object.values(this.answers).filter(a => a && a.is_doubt).length;
                },

                initEngine() {
                    // Update countdown timer every second
                    this.updateTimer();
                    this.timerInterval = setInterval(() => {
                        this.updateTimer();
                    }, 1000);

                    // Anti-Cheat Window Blur detection
                    window.addEventListener('blur', () => {
                        this.recordTabViolation();
                    });

                    // Disable standard copy-paste keyboard shortcuts
                    window.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'v' || e.key === 'u')) {
                            e.preventDefault();
                        }
                    });
                },

                updateTimer() {
                    const now = new Date().getTime();
                    const distance = this.endsAt - now;

                    if (distance <= 0) {
                        clearInterval(this.timerInterval);
                        this.remainingSeconds = 0;
                        this.timerDisplay = '00:00:00';
                        // Auto-submit when server end time is reached
                        window.location.href = this.finishUrl;
                        return;
                    }

                    this.remainingSeconds = Math.floor(distance / 1000);
                    const hours = Math.floor(this.remainingSeconds / 3600);
                    const minutes = Math.floor((this.remainingSeconds % 3600) / 60);
                    const seconds = this.remainingSeconds % 60;

                    this.timerDisplay = [hours, minutes, seconds]
                        .map(v => v < 10 ? '0' + v : v)
                        .join(':');
                },

                goToQuestion(idx) {
                    if (idx >= 0 && idx < this.questions.length) {
                        this.currentIndex = idx;
                        this.mobileNavOpen = false;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                prevQuestion() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                nextQuestion() {
                    if (this.currentIndex < this.questions.length - 1) {
                        this.currentIndex++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                selectSingleOption(optionId) {
                    const qId = this.currentQuestion.id;
                    const isDoubt = this.currentAnswer.is_doubt;
                    const newOptionIds = [optionId];

                    // Optimistic UI update
                    this.answers[qId] = {
                        option_ids: newOptionIds,
                        statement_values: {},
                        is_doubt: isDoubt
                    };

                    this.sendAnswerAjax(qId, newOptionIds, isDoubt);
                },

                toggleOption(optionId) {
                    const qId = this.currentQuestion.id;
                    const isDoubt = this.currentAnswer.is_doubt;
                    let currentIds = [...(this.currentAnswer.option_ids || [])];

                    if (currentIds.includes(optionId)) {
                        currentIds = currentIds.filter(id => id !== optionId);
                    } else {
                        currentIds.push(optionId);
                    }

                    // Optimistic UI update
                    this.answers[qId] = {
                        option_ids: currentIds,
                        statement_values: {},
                        is_doubt: isDoubt
                    };

                    this.sendAnswerAjax(qId, currentIds, isDoubt);
                },

                toggleDoubt() {
                    const q = this.currentQuestion;
                    const qId = q.id;
                    const newDoubt = !this.currentAnswer.is_doubt;

                    if (!this.answers[qId]) {
                        this.answers[qId] = { option_ids: [], statement_values: {}, is_doubt: false };
                    }
                    this.answers[qId].is_doubt = newDoubt;

                    if (q.type === 'statement') {
                        const vals = this.answers[qId].statement_values || {};
                        const firstOptId = q.options && q.options[0] ? q.options[0].id : null;
                        const firstVal = firstOptId ? vals[firstOptId] : null;
                        this.sendStatementAjax(qId, firstOptId, firstVal, newDoubt);
                    } else {
                        const ids = this.answers[qId].option_ids || [];
                        this.sendAnswerAjax(qId, ids, newDoubt);
                    }
                },

                async sendAnswerAjax(questionId, optionIds, isDoubt) {
                    this.saving = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(this.saveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                question_id: questionId,
                                option_ids: optionIds,
                                is_doubt: isDoubt
                            })
                        });

                        const data = await response.json();
                        if (response.status === 410 || data.expired) {
                            this.timeUpModal = true;
                            setTimeout(() => {
                                window.location.href = data.redirect || this.finishUrl;
                            }, 1800);
                        }
                    } catch (err) {
                        console.error('Auto-save error:', err);
                    } finally {
                        this.saving = false;
                    }
                },

                async sendStatementAjax(questionId, optionId, value, isDoubt) {
                    this.saving = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(this.saveUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                question_id: questionId,
                                option_id: optionId,
                                value: value,
                                is_doubt: isDoubt
                            })
                        });

                        const data = await response.json();
                        if (response.status === 410 || data.expired) {
                            this.timeUpModal = true;
                            setTimeout(() => {
                                window.location.href = data.redirect || this.finishUrl;
                            }, 1800);
                        }
                    } catch (err) {
                        console.error('Auto-save statement error:', err);
                    } finally {
                        this.saving = false;
                    }
                },

                async recordTabViolation() {
                    this.violations++;
                    this.violationToast = true;
                    setTimeout(() => {
                        this.violationToast = false;
                    }, 4000);

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        await fetch(this.violationUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            }
                        });
                    } catch (e) {}
                },

                getGridClass(qId, idx) {
                    const q = this.questions[idx];
                    const ans = this.answers[qId];
                    const isCurrent = (this.currentIndex === idx);

                    const isDoubt = ans && !!ans.is_doubt;
                    const isFull = this.isQuestionAnsweredFully(q);
                    const isPartial = this.isQuestionAnsweredPartially(q);

                    // ZERO black borders or clipping - 100% harmonious, clean palette
                    if (isDoubt) {
                        if (isCurrent) {
                            return 'bg-amber-500 text-white border-2 border-amber-300 shadow-md shadow-amber-500/25 font-black';
                        }
                        return 'bg-amber-500 text-white border border-amber-500 hover:bg-amber-600 font-bold shadow-2xs';
                    }

                    if (isFull) {
                        if (isCurrent) {
                            return 'bg-indigo-600 text-white border-2 border-indigo-300 shadow-md shadow-indigo-600/30 font-black';
                        }
                        return 'bg-indigo-600 text-white border border-indigo-600 hover:bg-indigo-700 font-bold shadow-2xs';
                    }

                    if (isPartial) {
                        if (isCurrent) {
                            return 'bg-sky-500 text-white border-2 border-sky-300 shadow-md shadow-sky-500/25 font-black';
                        }
                        return 'bg-sky-500 text-white border border-sky-500 hover:bg-sky-600 font-bold shadow-2xs';
                    }

                    // Belum Terjawab (Unanswered)
                    if (isCurrent) {
                        return 'bg-indigo-50/90 text-indigo-700 border-2 border-indigo-600 shadow-sm shadow-indigo-600/15 font-black';
                    }
                    return 'bg-slate-100 text-slate-700 border border-slate-200/80 hover:bg-slate-200 hover:border-slate-300 font-bold';
                }
            };
        }
    </script>
</body>
</html>
