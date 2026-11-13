@extends('layouts.admin')

@section('header-title', 'Ringkasan Sistem')

@section('content')
<div class="space-y-7 slide-up">

    {{-- ── Stat Cards ──────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Total Peserta --}}
        <div class="card p-6 flex items-center justify-between gap-4 hover-lift hover:shadow-md transition-all duration-200">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Peserta</p>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($totalUsers) }}</h3>
                <span class="text-[11px] text-slate-400 font-medium">Akun terdaftar</span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-indigo-50 border border-indigo-100/80 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>

        {{-- Subtest Aktif --}}
        <div class="card p-6 flex items-center justify-between gap-4 hover-lift hover:shadow-md transition-all duration-200">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Subtest Aktif</p>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($totalActiveSubtests) }}</h3>
                <span class="text-[11px] text-emerald-600 font-bold">● Siap dikerjakan</span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-emerald-50 border border-emerald-100/80 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
        </div>

        {{-- Total Pengerjaan --}}
        <div class="card p-6 flex items-center justify-between gap-4 hover-lift hover:shadow-md transition-all duration-200">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Pengerjaan</p>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($totalAttempts) }}</h3>
                <span class="text-[11px] text-slate-400 font-medium">Seluruh sesi ujian</span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-amber-50 border border-amber-100/80 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        {{-- Rata-rata Skor --}}
        <div class="card p-6 flex items-center justify-between gap-4 hover-lift hover:shadow-md transition-all duration-200">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Rata-rata Skor</p>
                <h3 class="text-3xl font-black text-slate-900">{{ number_format($globalAverageScore, 1) }}</h3>
                <span class="text-[11px] text-slate-400 font-medium">Skala 0 – 100</span>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-violet-50 border border-violet-100/80 text-violet-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Quick Actions ────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.subtests.index') }}"
           class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Buat Subtest Baru
        </a>
        <a href="{{ route('admin.users.index') }}"
           class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            Kelola Peserta
        </a>
        <a href="{{ route('admin.question-banks.index') }}"
           class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            Kelola Bank Soal
        </a>
        <a href="{{ route('admin.login-logs.index') }}"
           class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Monitoring Lokasi & IP Login
        </a>
    </div>

    {{-- ── Recent User Login & Visitor Monitoring (Lokasi, IP, Browser) ── --}}
    <div class="table-wrapper bg-white">
        <div class="p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Monitoring Lokasi, IP & Browser (Klik Link & Login)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Otomatis mendeteksi lokasi dan IP saat peserta mengklik link (tanpa harus login) untuk mencegah kecurangan</p>
            </div>
            <a href="{{ route('admin.login-logs.index') }}" class="btn-secondary btn-xs">
                Lihat Semua Log
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th whitespace-nowrap">Pengunjung / Pengguna</th>
                        <th class="table-th whitespace-nowrap">Lokasi Langsung</th>
                        <th class="table-th whitespace-nowrap">IP Address</th>
                        <th class="table-th whitespace-nowrap">Browser & Perangkat</th>
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
                                                    <span class="badge badge-indigo text-[10px] py-0 px-1.5">Peserta (Login)</span>
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
                                                (Peta)
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
                                Belum ada catatan aktivitas login pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Recent Activity ─────────────────────────── --}}
    <div class="table-wrapper bg-white">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Aktivitas Ujian Terbaru</h2>
                <p class="text-xs text-slate-500 mt-0.5">5 sesi ujian terakhir yang berhasil diselesaikan oleh peserta</p>
            </div>
            <span class="badge badge-indigo">Selesai Dinilai</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th whitespace-nowrap">Nama Peserta</th>
                        <th class="table-th whitespace-nowrap">Subtest</th>
                        <th class="table-th text-center whitespace-nowrap">Benar / Total</th>
                        <th class="table-th text-center whitespace-nowrap">Skor Akhir</th>
                        <th class="table-th text-right whitespace-nowrap">Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentAttempts as $attempt)
                        <tr class="table-row">
                            <td class="table-td whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-200 text-indigo-700 font-black text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($attempt->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $attempt->user->name ?? 'Pengguna Dihapus' }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $attempt->user->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td font-semibold text-slate-700 whitespace-nowrap">
                                {{ $attempt->subtest->name ?? 'Subtest Dihapus' }}
                            </td>
                            <td class="table-td text-center whitespace-nowrap">
                                <span class="badge badge-indigo text-xs">
                                    {{ $attempt->correct_count ?? 0 }} / {{ $attempt->subtest->total_questions ?? '-' }}
                                </span>
                            </td>
                            <td class="table-td text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-black
                                    {{ $attempt->score >= 70 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ number_format($attempt->score, 1) }}
                                </span>
                            </td>
                            <td class="table-td text-right whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200/80 text-xs text-slate-600 font-mono font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $attempt->finished_at ? $attempt->finished_at->format('d M Y, H:i') : '—' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <p class="font-semibold text-sm text-slate-500">Belum ada aktivitas ujian</p>
                                    <p class="text-xs">Aktivitas akan muncul di sini setelah peserta menyelesaikan ujian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
