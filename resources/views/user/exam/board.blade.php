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
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800 select-none"
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

    <!-- Top Floating Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                    T
                </span>
                <div>
                    <h1 class="font-bold text-slate-900 text-sm leading-tight">{{ $subtest->name }}</h1>
                    <span class="text-[11px] text-slate-400 font-medium">Tes Kompetensi Akademik (TKA)</span>
                </div>
            </div>

            <!-- Server-Authoritative Floating Countdown Timer -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl border bg-slate-900 text-white text-xs font-mono font-bold tracking-wider shadow-sm"
                    :class="remainingSeconds < 300 ? 'bg-rose-600 border-rose-500 animate-pulse' : 'bg-slate-900 border-slate-800'">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="timerDisplay">--:--:--</span>
                </div>

                <!-- Auto-Save Status Pill -->
                <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                    <span class="w-2 h-2 rounded-full" :class="saving ? 'bg-amber-400 animate-ping' : 'bg-emerald-500'"></span>
                    <span x-text="saving ? 'Menyimpan...' : 'Tersimpan'">Tersimpan</span>
                </div>

                <button type="button" @click="confirmFinishModal = true"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition">
                    <span>Selesaikan</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Exam Board Container -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
            
            <!-- Left: Main Question Area (col-span-3) -->
            <div class="lg:col-span-3 space-y-5">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8">
                    <!-- Question Header & Doubt Badge -->
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="px-3 py-1 rounded-xl bg-slate-900 text-white text-xs font-bold font-mono">
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
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Ragu-ragu</span>
                                </span>
                            </template>
                        </div>

                        <!-- Doubt Toggle Button -->
                        <button type="button" @click="toggleDoubt()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold transition"
                            :class="currentAnswer.is_doubt ? 'bg-amber-500 border-amber-600 text-white' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="currentAnswer.is_doubt ? 'Hapus Ragu-ragu' : 'Tandai Ragu-ragu'">Tandai Ragu-ragu</span>
                        </button>
                    </div>

                    <!-- Statement Type: Stimulus Reading Container & Notice -->
                    <template x-if="currentQuestion.type === 'statement'">
                        <div>
                            <div class="mb-5 p-5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 text-sm leading-relaxed whitespace-pre-line font-normal shadow-2xs">
                                <div class="flex items-center gap-2 mb-2.5 pb-2 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Stimulus / Teks Bacaan</span>
                                </div>
                                <div x-text="currentQuestion.text"></div>

                                <!-- Stimulus Image Attachment if exists -->
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

                            <div class="mb-4 flex items-center gap-2.5 p-3.5 rounded-2xl bg-teal-50/90 border border-teal-200 text-teal-900 text-xs font-semibold">
                                <svg class="w-4 h-4 text-teal-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Tentukan apakah setiap pernyataan berikut <strong>Sesuai</strong> atau <strong>Tidak Sesuai</strong> berdasarkan stimulus di atas.</span>
                            </div>

                            <!-- Statements List with Dual Toggles -->
                            <div class="space-y-3">
                                <template x-for="option in currentQuestion.options" :key="option.id">
                                    <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-slate-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex items-start gap-3 flex-1">
                                            <div class="w-7 h-7 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5"
                                                x-text="option.label">
                                            </div>
                                            <p class="text-sm font-medium text-slate-800 leading-relaxed" x-text="option.text"></p>
                                        </div>
                                        <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                                            <!-- Sesuai Button -->
                                            <button type="button"
                                                @click="setStatementValue(option.id, true)"
                                                class="px-4 py-2 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                                                :class="getStatementValue(option.id) === true ? 'bg-emerald-600 border-emerald-600 text-white shadow-xs' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>Sesuai</span>
                                            </button>

                                            <!-- Tidak Sesuai Button -->
                                            <button type="button"
                                                @click="setStatementValue(option.id, false)"
                                                class="px-4 py-2 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
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

                    <!-- Standard (Single & Multiple PGK) Type -->
                    <template x-if="currentQuestion.type !== 'statement'">
                        <div>
                            <!-- Question Content Text -->
                            <div class="prose prose-slate max-w-none text-base text-slate-900 leading-relaxed font-medium mb-4 whitespace-pre-line"
                                x-text="currentQuestion.text">
                            </div>

                            <!-- Question Image Attachment if exists -->
                            <template x-if="currentQuestion.image">
                                <div class="mb-6 flex flex-col items-center sm:items-start">
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

                            <!-- PGK Multiple Choice Notice Banner -->
                            <template x-if="currentQuestion.type === 'multiple'">
                                <div class="mb-4 flex items-center gap-2.5 p-3.5 rounded-2xl bg-indigo-50/90 border border-indigo-200 text-indigo-900 text-xs font-semibold">
                                    <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Pilihan Ganda Kompleks — Jawaban benar bisa lebih dari satu. Pilih semua opsi yang menurut Anda benar.</span>
                                </div>
                            </template>

                            <!-- Options Cards (Single Radio vs Multi Checkbox) -->
                            <div class="space-y-3">
                                <template x-for="option in currentQuestion.options" :key="option.id">
                                    <label class="flex items-start gap-3.5 p-4 rounded-2xl border transition cursor-pointer group select-none"
                                        :class="isOptionSelected(option.id) ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-600' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'">
                                        
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

                                        <!-- Multi-select Checkbox Square Indicator -->
                                        <template x-if="currentQuestion.type === 'multiple'">
                                            <div class="w-5 h-5 mt-1 rounded-md border flex items-center justify-center flex-shrink-0 transition"
                                                :class="isOptionSelected(option.id) ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-300 bg-white group-hover:border-slate-400'">
                                                <svg x-show="isOptionSelected(option.id)" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </template>
                                        
                                        <div class="w-8 h-8 rounded-xl font-bold text-xs flex items-center justify-center flex-shrink-0 transition"
                                            :class="isOptionSelected(option.id) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'"
                                            x-text="option.label">
                                        </div>

                                        <div class="text-sm text-slate-800 pt-1 font-medium leading-normal flex-1"
                                            x-text="option.text">
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Bottom Navigation Buttons -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="prevQuestion()" :disabled="currentIndex === 0"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        <span>Soal Sebelumnya</span>
                    </button>

                    <template x-if="currentIndex < questions.length - 1">
                        <button type="button" @click="nextQuestion()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-indigo-600 transition shadow-xs">
                            <span>Soal Selanjutnya</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </template>

                    <template x-if="currentIndex === questions.length - 1">
                        <button type="button" @click="confirmFinishModal = true"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                            <span>Selesaikan Ujian</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Right: Question Number Grid Sidebar (col-span-1) -->
            <div class="lg:col-span-1 space-y-4 sticky top-22">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">Navigasi Soal</h2>
                        <span class="text-xs font-semibold text-slate-500 font-mono" x-text="answeredCount + '/' + questions.length"></span>
                    </div>

                    <!-- Legend -->
                    <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-500 pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-md bg-indigo-600"></span>
                            <span>Lengkap</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-md bg-sky-500"></span>
                            <span>Sebagian</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-md bg-amber-500"></span>
                            <span>Ragu-ragu</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-md bg-slate-200"></span>
                            <span>Belum</span>
                        </div>
                    </div>

                    <!-- Grid Numbers (1 to N) -->
                    <div class="grid grid-cols-5 gap-2 max-h-72 overflow-y-auto pr-1">
                        <template x-for="(q, idx) in questions" :key="q.id">
                            <button type="button" @click="goToQuestion(idx)"
                                class="h-10 rounded-xl font-bold text-xs flex items-center justify-center transition"
                                :class="getGridClass(q.id, idx)">
                                <span x-text="idx + 1"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Finish CTA Button -->
                    <div class="pt-2 border-t border-slate-100">
                        <button type="button" @click="confirmFinishModal = true"
                            class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition">
                            Kumpulkan Jawaban
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Anti-Cheat Violation Toast Notification -->
    <div x-show="violationToast" style="display: none;"
        class="fixed bottom-6 right-6 z-50 p-4 rounded-2xl bg-rose-600 text-white text-xs font-semibold shadow-xl flex items-center gap-3 border border-rose-500">
        <svg class="w-5 h-5 flex-shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="font-bold">Peringatan Integritas Ujian!</p>
            <p class="text-[11px] text-rose-100">Anda terdeteksi meninggalkan jendela ujian (<span x-text="violations"></span> kali).</p>
        </div>
    </div>

    <!-- Confirm Submit Modal -->
    <div x-show="confirmFinishModal" style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
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

            <!-- Summary Chips -->
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
                    class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition">
                    Ya, Selesaikan & Lihat Skor
                </button>
                <button type="button" @click="confirmFinishModal = false"
                    class="w-full py-2.5 px-4 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Kembali Mengerjakan
                </button>
            </form>
        </div>
    </div>

    <!-- Time's Up Modal (Full Custom) -->
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

    <!-- Image Lightbox Modal -->
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

    <!-- Alpine.js Exam Engine Component Logic -->
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
                    } else {
                        if (Array.isArray(ans.option_ids)) return ans.option_ids.length > 0;
                        return ans.option_id !== null && ans.option_id !== undefined;
                    }
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
                    }
                },

                prevQuestion() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                    }
                },

                nextQuestion() {
                    if (this.currentIndex < this.questions.length - 1) {
                        this.currentIndex++;
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
                        this.sendAnswerAjax(qId, [], newDoubt);
                    } else {
                        const optIds = this.currentAnswer.option_ids || [];
                        this.sendAnswerAjax(qId, optIds, newDoubt);
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

                    let base = '';
                    if (ans && ans.is_doubt) {
                        base = 'bg-amber-500 text-white shadow-2xs';
                    } else if (this.isQuestionAnsweredFully(q)) {
                        base = 'bg-indigo-600 text-white shadow-2xs';
                    } else if (this.isQuestionAnsweredPartially(q)) {
                        base = 'bg-sky-500 text-white shadow-2xs';
                    } else {
                        base = 'bg-slate-100 text-slate-600 hover:bg-slate-200';
                    }

                    if (isCurrent) {
                        base += ' ring-2 ring-slate-900 ring-offset-2';
                    }

                    return base;
                }
            };
        }
    </script>
</body>
</html>
