@extends('layouts.user')

@section('title', 'Dashboard Peserta')

@section('content')
<div class="space-y-7">

    {{-- ── Welcome Banner ──────────────────────────── --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 to-indigo-950 p-7 sm:p-9 text-white">
        {{-- Decorative orbs --}}
        <div class="absolute top-0 right-0 w-72 h-72 bg-indigo-600/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-violet-600/15 rounded-full blur-2xl translate-y-1/2 -translate-x-1/4 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold border border-indigo-400/25 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                    Sesi Simulasi Aktif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white mb-2">
                    Halo, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="text-slate-300 text-sm max-w-lg leading-relaxed">
                    Selamat datang di platform Tes Kompetensi Akademik (TKA). Pilih salah satu subtest di bawah untuk memulai atau melanjutkan pengerjaan soal.
                </p>
            </div>

            {{-- Progress stats --}}
            <div class="flex items-center gap-5 bg-white/8 backdrop-blur-sm border border-white/10 rounded-2xl px-6 py-5 flex-shrink-0">
                <div class="text-center">
                    <span class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Kemajuan</span>
                    <span class="text-2xl font-black text-white">{{ $completedCount }}<span class="text-slate-500 text-lg font-semibold"> / {{ $totalSubtests }}</span></span>
                    <span class="block text-[11px] text-slate-400 font-medium mt-0.5">Subtest Selesai</span>
                </div>
                <div class="w-px h-10 bg-white/10"></div>
                <div class="text-center">
                    <span class="block text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Rata-rata</span>
                    <span class="text-2xl font-black text-indigo-300">{{ number_format($averageScore, 1) }}</span>
                    <span class="block text-[11px] text-slate-400 font-medium mt-0.5">Dari 100</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Bank Soal Promo ───────────────────────── --}}
    <div class="rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-700 p-6 sm:p-7 text-white shadow-lg shadow-indigo-600/20 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm sm:text-base font-extrabold text-white leading-snug">
                    Ingin Belajar dari Bank Soal Bergambar?
                </h2>
                <p class="text-xs text-indigo-100 mt-0.5 max-w-xl">
                    Jelajahi arsip soal bergambar, rumus, dan diagram dalam mode penampil slide layar penuh.
                </p>
            </div>
        </div>
        <a href="{{ route('user.question-banks.index') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white text-indigo-900 text-xs font-black hover:bg-indigo-50 shadow-sm transition whitespace-nowrap self-start sm:self-center hover-lift">
            <span>Buka Bank Soal</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    {{-- ── Subtest Grid ─────────────────────────────── --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-900">Mata Uji Subtest Tersedia</h2>
            <span class="text-xs text-slate-400 font-semibold">{{ $subtests->count() }} Subtest Siap</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($subtests as $subtest)
                @php
                    $session = $sessions->get($subtest->id);
                    $isFinished = $session && $session->status === 'finished';
                    $isOngoing  = $session && $session->status === 'ongoing' && !$session->isExpired();
                    $isExpired  = $session && $session->status === 'ongoing' && $session->isExpired();
                @endphp

                <div class="card-hover flex flex-col justify-between overflow-hidden group">
                    <div class="p-6 space-y-4">
                        {{-- Header: icon + badge --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 font-black text-sm flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all duration-200">
                                {{ strtoupper(substr($subtest->name, 0, 2)) }}
                            </div>

                            @if ($isFinished)
                                <span class="badge badge-emerald text-[11px]">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Skor: {{ number_format($session->score, 1) }}
                                </span>
                            @elseif ($isOngoing)
                                <span class="badge badge-indigo text-[11px] animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    Berlangsung
                                </span>
                            @else
                                <span class="badge badge-slate text-[11px]">Belum Dikerjakan</span>
                            @endif
                        </div>

                        {{-- Title & Description --}}
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors duration-200">
                                {{ $subtest->name }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                {{ $subtest->description ?? 'Simulasi tes objektif terstandar berbasis komputer.' }}
                            </p>
                        </div>

                        {{-- Meta badges --}}
                        <div class="flex items-center gap-4 pt-3 border-t border-slate-100 text-xs font-semibold text-slate-500">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                {{ $subtest->questions_count }} Soal
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $subtest->duration_minutes }} Menit
                            </div>
                        </div>
                    </div>

                    {{-- CTA Button --}}
                    <div class="p-4 bg-slate-50/70 border-t border-slate-100">
                        @if ($isFinished)
                            <a href="{{ route('user.exam.result', $subtest) }}"
                               class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-sm shadow-emerald-600/20 transition-all duration-150">
                                Lihat Hasil & Pembahasan
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @elseif ($isOngoing)
                            <a href="{{ route('user.exam.board', $subtest) }}"
                               class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-sm shadow-indigo-600/20 transition-all duration-150">
                                Lanjutkan Ujian
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('user.exam.start', $subtest) }}"
                               class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-xs font-black shadow-sm transition-all duration-200 group-hover:bg-indigo-600 group-hover:shadow-indigo-600/20">
                                Mulai Ujian
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

            @empty
                <div class="col-span-full">
                    <div class="card p-16 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-300 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-slate-700 font-bold text-sm">Belum ada subtest yang tersedia</p>
                        <p class="text-slate-400 text-xs mt-1.5">Silakan hubungi administrator untuk membuka jadwal ujian simulasi.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
