@extends('layouts.user')

@section('title', 'Katalog Bank Soal — TKA CBT')

@section('content')
<div class="space-y-7">

    {{-- ── Hero Banner ──────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-8 sm:p-10 text-white">
        <div class="absolute -right-16 -top-16 w-80 h-80 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-8 -bottom-8 w-56 h-56 bg-violet-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 max-w-2xl space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Bank Soal &amp; Modul Latihan</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight text-white">
                Koleksi Bank Soal &amp; Diagram TKA
            </h1>
            <p class="text-slate-300 text-sm leading-relaxed">
                Pelajari ribuan arsip soal bergambar, rumus matematika, dan diagram yang telah dikurasi oleh tim akademik untuk persiapan tes kompetensi Anda.
            </p>
        </div>
    </div>

    {{-- ── Filter & Search ─────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a href="{{ route('user.question-banks.index') }}"
               class="px-3.5 py-2 rounded-xl font-bold transition-all duration-150 whitespace-nowrap
                      {{ !request('category') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200' }}">
                Semua Kategori
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('user.question-banks.index', ['category' => $cat]) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all duration-150 whitespace-nowrap
                          {{ request('category') === $cat ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'bg-white border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('user.question-banks.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
            @if (request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <div class="relative flex-1 sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bank soal..."
                       class="form-input pl-9 text-sm w-full">
            </div>
            <button type="submit" class="btn-primary btn-sm flex-shrink-0">Cari</button>
            @if (request('search') || request('category'))
                <a href="{{ route('user.question-banks.index') }}" class="text-xs text-slate-400 hover:text-rose-600 font-semibold transition flex-shrink-0">Reset</a>
            @endif
        </form>
    </div>

    {{-- ── Cards Grid ───────────────────────────────── --}}
    @if ($banks->isEmpty())
        <div class="card p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 mb-1.5">Belum Ada Bank Soal</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto">
                Belum ada arsip bank soal yang dipublikasikan pada kategori ini. Silakan periksa kembali nanti.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($banks as $bank)
                <div class="card-hover flex flex-col justify-between overflow-hidden group">
                    <div class="p-6 space-y-3">
                        {{-- Category + photo count --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="badge badge-indigo uppercase tracking-wide text-[10px]">
                                {{ $bank->category ?: 'Mata Uji Umum' }}
                            </span>
                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-500 bg-slate-50 border border-slate-100 px-2.5 py-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $bank->items_count }} Foto
                            </div>
                        </div>

                        {{-- Title & Description --}}
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 leading-snug mb-1.5 group-hover:text-indigo-600 transition-colors duration-200">
                                {{ $bank->title }}
                            </h3>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">
                                {{ $bank->description ?: 'Kumpulan arsip soal bergambar dan materi latihan untuk meningkatkan pemahaman Anda.' }}
                            </p>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-100">
                        <a href="{{ route('user.question-banks.show', $bank) }}"
                           class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm shadow-indigo-600/20 transition-all duration-150">
                            Buka &amp; Pelajari Soal
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 pagination-wrapper">
            {{ $banks->links() }}
        </div>
    @endif

</div>
@endsection
