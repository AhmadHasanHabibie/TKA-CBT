@extends('layouts.admin')

@section('header-title', 'Dashboard Dokumentasi & Log Aktivitas')

@section('content')
<div class="space-y-7 slide-up">

    {{-- ── Stat Cards ──────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Total Siswa --}}
        <div class="card p-6 flex items-center justify-between gap-4 hover-lift hover:shadow-md transition-all duration-200">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Akun Siswa</p>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($totalUsers) }}</h3>
                <span class="text-[11px] text-slate-400 font-medium">Siswa terdaftar</span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-indigo-50 border border-indigo-100/80 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>

        {{-- Album Dokumentasi Aktif --}}
        <div class="card p-6 flex items-center justify-between gap-4 hover-lift hover:shadow-md transition-all duration-200">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Album Dokumentasi</p>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($totalActiveAlbums ?? 0) }}</h3>
                <span class="text-[11px] text-emerald-600 font-bold">● Dipublikasikan</span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-emerald-50 border border-emerald-100/80 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>

        {{-- Total Foto Dokumentasi --}}
        <div class="card p-6 flex items-center justify-between gap-4 hover-lift hover:shadow-md transition-all duration-200">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Foto Siswa</p>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($totalPhotos ?? 0) }}</h3>
                <span class="text-[11px] text-slate-400 font-medium">Koleksi foto tersimpan</span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-amber-50 border border-amber-100/80 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        {{-- Total Log Aktivitas & Lokasi --}}
        <div class="card p-6 flex items-center justify-between gap-4 hover-lift hover:shadow-md transition-all duration-200">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Log Aktivitas &amp; IP</p>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($totalLogs ?? 0) }}</h3>
                <span class="text-[11px] text-violet-600 font-bold">● Klik link &amp; login</span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-violet-50 border border-violet-100/80 text-violet-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Quick Actions ────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.question-banks.index') }}"
           class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            Kelola Album Dokumentasi Foto
        </a>
        <a href="{{ route('admin.users.index') }}"
           class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            Kelola Data Siswa
        </a>
        <a href="{{ route('admin.login-logs.index') }}"
           class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Log Aktivitas, Lokasi &amp; IP
        </a>
    </div>

    {{-- ── Recent User Login & Visitor Monitoring (Lokasi, IP, Browser) ── --}}
    <div class="table-wrapper bg-white">
        <div class="p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Log Aktivitas Pengunjung &amp; Siswa (Lokasi, IP &amp; Browser)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Otomatis mendeteksi lokasi geografis, alamat IP, dan browser begitu link diklik (tanpa harus login)</p>
            </div>
            <a href="{{ route('admin.login-logs.index') }}" class="btn-secondary btn-xs">
                Lihat Semua Log Aktivitas
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th whitespace-nowrap">Pengunjung / Siswa</th>
                        <th class="table-th whitespace-nowrap">Lokasi Langsung</th>
                        <th class="table-th whitespace-nowrap">IP Address</th>
                        <th class="table-th whitespace-nowrap">Browser &amp; Perangkat</th>
                        <th class="table-th text-right whitespace-nowrap">Waktu Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentLogins ?? [] as $log)
                        <tr class="table-row">
                            <td class="table-td whitespace-nowrap">
                                @if ($log->user)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs flex-shrink-0
                                            {{ $log->user->role === 'admin' ? 'bg-gradient-to-br from-purple-500 to-indigo-600 text-white' : 'bg-gradient-to-br from-indigo-100 to-indigo-200 text-indigo-700' }}">
                                            {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-bold text-slate-900 text-sm">{{ $log->user->name }}</p>
                                                @if ($log->user->role === 'admin')
                                                    <span class="badge badge-purple text-[10px] py-0 px-1.5">Admin</span>
                                                @else
                                                    <span class="badge badge-indigo text-[10px] py-0 px-1.5">Siswa (Login)</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $log->user->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 border border-amber-200 text-amber-700 flex items-center justify-center font-black text-xs flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <p class="font-bold text-slate-900 text-sm">Pengunjung (Klik Link)</p>
                                                <span class="inline-flex items-center px-1.5 py-0 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Tanpa Login
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-400 font-mono truncate max-w-[180px]">{{ $log->visited_url ?: url('/login') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="table-td">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <span class="text-xs font-bold text-slate-800">{{ $log->location ?: 'Tidak Diketahui' }}</span>
                                        @if ($log->map_url)
                                            <a href="{{ $log->map_url }}" target="_blank" rel="noopener noreferrer"
                                               class="ml-1 text-[10px] font-bold text-indigo-600 hover:underline">
                                                (Buka Peta)
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="table-td whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs font-mono font-bold text-slate-700">
                                    {{ $log->ip_address ?: '—' }}
                                </span>
                            </td>
                            <td class="table-td whitespace-nowrap">
                                <div class="text-xs font-bold text-slate-800">{{ $log->browser ?: 'Unknown Browser' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $log->os ?: 'Unknown OS' }} ({{ $log->device_type ?: 'Desktop' }})</div>
                            </td>
                            <td class="table-td text-right whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200/80 text-xs text-slate-600 font-mono font-medium">
                                    {{ $log->logged_in_at ? $log->logged_in_at->format('d M Y, H:i') : '—' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400 text-xs">
                                Belum ada catatan aktivitas akses.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Latest Photo Documentation Albums ─────────── --}}
    <div class="table-wrapper bg-white">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Album Dokumentasi Foto Terbaru</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar album dokumentasi kegiatan siswa yang baru ditambahkan</p>
            </div>
            <a href="{{ route('admin.question-banks.index') }}" class="btn-primary btn-xs">
                + Tambah Album
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th whitespace-nowrap">Judul Album</th>
                        <th class="table-th whitespace-nowrap">Kategori Kegiatan</th>
                        <th class="table-th text-center whitespace-nowrap">Jumlah Foto</th>
                        <th class="table-th text-center whitespace-nowrap">Status</th>
                        <th class="table-th text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($latestAlbums ?? [] as $album)
                        <tr class="table-row">
                            <td class="table-td">
                                <p class="font-bold text-slate-900 text-sm">{{ $album->title }}</p>
                                <p class="text-[11px] text-slate-400 line-clamp-1">{{ $album->description ?: 'Dokumentasi kegiatan siswa' }}</p>
                            </td>
                            <td class="table-td whitespace-nowrap">
                                <span class="badge badge-indigo text-xs">{{ $album->category ?: 'Kegiatan Umum' }}</span>
                            </td>
                            <td class="table-td text-center whitespace-nowrap">
                                <span class="font-bold text-slate-800 text-xs">{{ $album->items_count }} Foto</span>
                            </td>
                            <td class="table-td text-center whitespace-nowrap">
                                @if ($album->is_active)
                                    <span class="badge badge-emerald text-xs">Publik</span>
                                @else
                                    <span class="badge badge-slate text-xs">Draft</span>
                                @endif
                            </td>
                            <td class="table-td text-right whitespace-nowrap">
                                <a href="{{ route('admin.question-banks.items', $album) }}" class="btn-secondary btn-xs">
                                    Kelola Foto
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                                Belum ada album dokumentasi foto. Klik tombol <strong>Kelola Album Dokumentasi Foto</strong> untuk membuat album pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
