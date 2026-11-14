@extends('layouts.admin')

@section('header-title', 'Log Aktivitas, Lokasi & IP Pengunjung')

@section('content')
<div class="space-y-6 slide-up" x-data="{
    copied: false,
    shareLink: '{{ url('/login') }}',
    copyShareLink() {
        navigator.clipboard.writeText(this.shareLink);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    },
    confirmDelete(id, userName) {
        window.showConfirm({
            type: 'danger',
            title: 'Hapus Riwayat Akses',
            message: 'Riwayat akses milik &ldquo;' + userName + '&rdquo; ini akan dihapus dari log sistem.',
            confirmText: 'Ya, Hapus Log',
            action: () => this.$refs['deleteLog' + id].submit()
        });
    },
    confirmClearAll() {
        window.showConfirm({
            type: 'danger',
            title: 'Bersihkan Seluruh Log Aktivitas',
            message: 'Seluruh catatan riwayat klik link, lokasi, IP address, dan browser akan dihapus permanen.',
            confirmText: 'Ya, Bersihkan Semua',
            action: () => this.$refs['clearAllLogsForm'].submit()
        });
    }
}">

    {{-- ── Instant Link Tracker Banner ── --}}
    <div class="card p-5 bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-950 text-white border-0 shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Deteksi Otomatis Aktif (Tanpa Perlu Login)
                </div>
                <h3 class="text-base font-black text-white">Link Galeri Dokumentasi Foto Siswa</h3>
                <p class="text-xs text-slate-300">
                    Begitu siswa atau pengunjung mengklik link galeri di bawah ini (bahkan <strong>sebelum login</strong>), sistem otomatis mencatat <strong>IP Address</strong>, <strong>Lokasi Geografis / GPS</strong>, dan <strong>Browser</strong> yang dipakai.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                <div class="px-3.5 py-2 rounded-xl bg-white/10 border border-white/15 font-mono text-xs text-indigo-200 select-all">
                    {{ url('/login') }}
                </div>
                <button type="button" @click="copyShareLink()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-md cursor-pointer">
                    <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <svg x-show="copied" x-cloak class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="copied ? 'Link Tersalin!' : 'Salin Link Galeri'">Salin Link Galeri</span>
                </button>
                <a href="{{ route('admin.login-logs.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh
                </a>
            </div>
        </div>
    </div>

    {{-- ── KPI Summary Cards ────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="card p-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Log Aktivitas</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($totalLogs) }}</h3>
                <span class="text-[11px] text-slate-400 font-medium">Klik link + sesi login</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>

        <div class="card p-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Klik Link (Belum Login)</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($guestClicks) }}</h3>
                <span class="text-[11px] text-amber-600 font-bold">● Terdeteksi saat buka link</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
            </div>
        </div>

        <div class="card p-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Percobaan Gagal Login</p>
                <h3 class="text-2xl font-black text-rose-600">{{ number_format($failedLogins) }}</h3>
                <span class="text-[11px] text-rose-600 font-bold">● Email dicoba tercatat</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <div class="card p-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">IP Address Unik</p>
                <h3 class="text-2xl font-black text-slate-900">{{ number_format($uniqueIps) }}</h3>
                <span class="text-[11px] text-emerald-600 font-bold">● Jaringan terdeteksi</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
            </div>
        </div>

        <div class="card p-5 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Browser Terpopuler</p>
                <h3 class="text-base font-black text-slate-900 truncate" title="{{ $topBrowser }}">{{ $topBrowser }}</h3>
                <span class="text-[11px] text-slate-400 font-medium">Paling sering dipakai</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-violet-50 border border-violet-100 text-violet-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Page Header & Filters ─────────────────────── --}}
    <div class="page-header">
        <div>
            <h2 class="page-title">Log Lokasi, IP Address & Browser User</h2>
            <p class="page-subtitle">Menampilkan seluruh aktivitas pengunjung saat klik link (tanpa login), percobaan login gagal, maupun setelah siswa login</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <form method="GET" action="{{ route('admin.login-logs.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="role" onchange="this.form.submit()" class="form-input text-xs py-2 bg-white w-56">
                    <option value="">Semua Aktivitas</option>
                    <option value="guest" {{ $role === 'guest' ? 'selected' : '' }}>Klik Link (Belum Login)</option>
                    <option value="failed" {{ $role === 'failed' ? 'selected' : '' }}>Percobaan Gagal Login (Email Dicoba)</option>
                    <option value="user" {{ $role === 'user' ? 'selected' : '' }}>Siswa (Sudah Login)</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari IP, lokasi, browser, nama..."
                           class="form-input pl-9 text-xs py-2 w-60 sm:w-72">
                </div>
            </form>

            @if ($totalLogs > 0)
                <form x-ref="clearAllLogsForm" method="POST" action="{{ route('admin.login-logs.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="confirmClearAll()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-bold transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Bersihkan Log
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ── Login & Visitor Logs Table ────────────────── --}}
    <div class="table-wrapper bg-white">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th whitespace-nowrap">Pengunjung / Siswa</th>
                        <th class="table-th whitespace-nowrap">Lokasi Langsung</th>
                        <th class="table-th whitespace-nowrap">IP Address</th>
                        <th class="table-th whitespace-nowrap">Browser &amp; Perangkat</th>
                        <th class="table-th whitespace-nowrap">Waktu Akses</th>
                        <th class="table-th text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="table-row">
                            {{-- Pengunjung / Pengguna --}}
                            <td class="table-td whitespace-nowrap">
                                @if (in_array($log->event_type, ['search_documentation', 'search_empty', 'search_found', 'search_not_found']))
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full {{ $log->event_type === 'search_empty' ? 'bg-amber-100 border border-amber-200 text-amber-600' : ($log->event_type === 'search_found' ? 'bg-emerald-100 border border-emerald-200 text-emerald-600' : 'bg-indigo-100 border border-indigo-200 text-indigo-600') }} flex items-center justify-center font-black text-xs flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-slate-900">
                                                    @if($log->event_type === 'search_empty')
                                                        Cari Dokumentasi (Input Kosong)
                                                    @elseif($log->event_type === 'search_found')
                                                        Pencarian Ditemukan
                                                    @else
                                                        Pencarian Tidak Ditemukan
                                                    @endif
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $log->event_type === 'search_empty' ? 'bg-amber-50 text-amber-700 border border-amber-200' : ($log->event_type === 'search_found' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-300') }}">
                                                    {{ $log->event_type === 'search_empty' ? 'Harap Isi Data' : ($log->event_type === 'search_found' ? 'Ditemukan' : 'Tidak Ditemukan') }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-700 space-x-2">
                                                <span>Sekolah: <strong class="text-indigo-600 font-bold">{{ $log->school_name ?: '(Kosong)' }}</strong></span>
                                                <span class="text-slate-300">·</span>
                                                <span>Email: <strong class="text-slate-900 font-mono">{{ $log->attempted_email ?: '(Kosong)' }}</strong></span>
                                            </p>
                                        </div>
                                    </div>
                                @elseif ($log->event_type === 'failed_login')
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center font-black text-xs flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-rose-700">Percobaan Gagal Login</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Email/Sandi Salah
                                                </span>
                                            </div>
                                            <p class="text-[11px] font-mono text-slate-700">
                                                Email dicoba: <span class="text-rose-600 font-bold">{{ $log->attempted_email ?: ($log->user?->email ?: '—') }}</span>
                                            </p>
                                        </div>
                                    </div>
                                @elseif ($log->user)
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs flex-shrink-0
                                            {{ $log->user->role === 'admin' ? 'bg-gradient-to-br from-purple-500 to-indigo-600 text-white' : 'bg-gradient-to-br from-indigo-100 to-slate-200 text-indigo-700' }}">
                                            {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-slate-900">{{ $log->user->name }}</span>
                                                @if ($log->user->role === 'admin')
                                                    <span class="badge badge-purple text-[10px] py-0.5 px-2">Admin</span>
                                                @else
                                                    <span class="badge badge-indigo text-[10px] py-0.5 px-2">Siswa (Login)</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] font-mono text-slate-400">{{ $log->user->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-amber-100 border border-amber-200 text-amber-700 flex items-center justify-center font-black text-xs flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-slate-900">Pengunjung (Klik Link)</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Tanpa Login
                                                </span>
                                            </div>
                                            <p class="text-[11px] font-mono text-slate-400 truncate max-w-[220px]" title="{{ $log->visited_url }}">
                                                {{ $log->visited_url ?: url('/login') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </td>

                            {{-- Lokasi Langsung --}}
                            <td class="table-td">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">{{ $log->location ?: 'Tidak Diketahui' }}</p>
                                        @if ($log->latitude !== null && $log->longitude !== null)
                                            <p class="text-[10px] font-mono text-slate-400">
                                                GPS: {{ number_format($log->latitude, 4) }}, {{ number_format($log->longitude, 4) }}
                                            </p>
                                        @endif
                                        @if ($log->map_url)
                                            <a href="{{ $log->map_url }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 mt-0.5">
                                                <span>Lihat Lokasi di Google Maps</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- IP Address --}}
                            <td class="table-td whitespace-nowrap">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs font-mono font-bold text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                        {{ $log->ip_address ?: '—' }}
                                    </span>
                                    @if (!empty($sharedIps[$log->ip_address]) && $sharedIps[$log->ip_address] > 1)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                            Dipakai {{ $sharedIps[$log->ip_address] }}x akses
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Browser & Perangkat --}}
                            <td class="table-td">
                                <div class="space-y-0.5" title="{{ $log->user_agent }}">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                        <svg class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $log->browser ?: 'Unknown Browser' }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-medium pl-5">
                                        {{ $log->os ?: 'Unknown OS' }} · <span class="text-slate-400">{{ $log->device_type ?: 'Desktop' }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Waktu Akses --}}
                            <td class="table-td whitespace-nowrap">
                                <div class="text-xs font-mono font-semibold text-slate-700">
                                    {{ $log->logged_in_at ? $log->logged_in_at->format('d M Y, H:i:s') : '—' }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $log->logged_in_at ? $log->logged_in_at->diffForHumans() : '' }}
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="table-td text-right whitespace-nowrap">
                                <form x-ref="deleteLog{{ $log->id }}" method="POST" action="{{ route('admin.login-logs.destroy', $log) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            @click="confirmDelete({{ $log->id }}, {{ Js::from($log->user->name ?? 'Pengunjung (' . $log->ip_address . ')') }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 text-[11px] font-bold transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <p class="font-bold text-sm text-slate-500">Belum ada riwayat akses tercatat</p>
                                    <p class="text-xs">Begitu seseorang mengklik link galeri (meskipun belum login), IP address, lokasi, dan browser mereka langsung muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="p-4 border-t border-slate-100 pagination-wrapper">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
