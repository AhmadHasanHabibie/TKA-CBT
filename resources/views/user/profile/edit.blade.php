@extends('layouts.user')

@section('title', 'Profil Saya')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- ── Back to Dashboard Navigation ─────────────────────── --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('user.dashboard') }}"
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition group">
            <div class="w-7 h-7 rounded-xl bg-white border border-slate-200 flex items-center justify-center group-hover:border-indigo-200 group-hover:bg-indigo-50/50 shadow-2xs transition">
                <svg class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform text-slate-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </div>
            <span>Kembali ke Dashboard</span>
        </a>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Akun Terverifikasi
        </span>
    </div>

    {{-- ── Main Responsive Grid (Desktop 2-Col, Mobile Stacked) ─ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ── Left Side: User Summary & Stats Card (4 Cols) ──── --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Profile Identity Card --}}
            <div class="card p-6 text-center space-y-4 shadow-sm border border-slate-200/80 slide-up">
                <div class="relative mx-auto w-24 h-24">
                    {{-- Big Avatar Ring --}}
                    <div class="w-24 h-24 rounded-3xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-600 text-white font-black text-3xl flex items-center justify-center shadow-lg shadow-indigo-600/30 border-4 border-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white shadow-xs" title="Akun Aktif">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight leading-snug break-words">
                        {{ $user->name }}
                    </h2>
                    <p class="text-xs text-slate-400 font-mono mt-0.5 break-all">
                        {{ $user->email }}
                    </p>
                    <div class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Peserta Ujian CBT</span>
                    </div>
                </div>

                {{-- Activity Counters --}}
                <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 text-left">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Subtest Selesai</span>
                        <span class="text-xl font-black text-slate-900 mt-0.5 block">{{ $completedSessions }}</span>
                        <span class="text-[10px] text-slate-400">Mata Uji</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Skor</span>
                        <span class="text-xl font-black text-indigo-600 mt-0.5 block">{{ number_format($avgScore, 1) }}</span>
                        <span class="text-[10px] text-slate-400">Skala 100</span>
                    </div>
                </div>

                <div class="pt-2 text-center text-[11px] text-slate-400">
                    Bergabung sejak <strong class="text-slate-600">{{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</strong>
                </div>
            </div>

            {{-- Informational Notice Card --}}
            <div class="p-5 rounded-2xl bg-gradient-to-br from-indigo-50/90 to-violet-50/70 border border-indigo-100/80 text-indigo-950 text-xs space-y-2">
                <div class="flex items-center gap-2 font-extrabold text-indigo-900">
                    <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Privasi & Fleksibilitas</span>
                </div>
                <p class="text-slate-600 leading-relaxed">
                    Anda bebas mengganti username dan password kapan pun. Sistem tidak meminta password lama agar Anda tetap mudah mengakses akun saat lupa sandi lama.
                </p>
            </div>

        </div>

        {{-- ── Right Side: Edit Forms (8 Cols) ────────────────── --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Card 1: Ganti Username / Nama Lengkap --}}
            <div class="card p-6 sm:p-7 border border-slate-200/80 shadow-sm slide-up">
                <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Informasi Username</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Ubah nama pengguna yang ditampilkan di sistem ujian</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('user.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Username Input --}}
                    <div>
                        <label for="name" class="form-label font-bold text-slate-800">
                            Username / Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required minlength="2" maxlength="100"
                                   class="form-input pl-10 text-sm font-medium @error('name') is-invalid @enderror"
                                   placeholder="Contoh: Budi Santoso">
                        </div>
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="form-hint">Nama ini akan digunakan pada seluruh lembar pengerjaan dan hasil nilai.</p>
                    </div>

                    {{-- Email (Read-only Info) --}}
                    <div>
                        <label for="email" class="form-label font-bold text-slate-800">
                            Alamat Email (Akun Login)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input type="email" id="email" value="{{ $user->email }}" disabled
                                   class="form-input pl-10 pr-24 text-sm font-medium bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-200/60 px-2 py-0.5 rounded-md">
                                    Terkunci
                                </span>
                            </div>
                        </div>
                        <p class="form-hint">Email bersifat permanen sebagai identitas unik login akun Anda.</p>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                                class="btn-primary w-full sm:w-auto px-6 py-2.5 text-xs font-bold rounded-xl shadow-sm transition active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Username</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card 2: Ganti Password Baru (Bebas tanpa password lama) --}}
            <div class="card p-6 sm:p-7 border border-slate-200/80 shadow-sm slide-up"
                 x-data="{ showNew: false, showConfirm: false }">
                
                <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Ganti Password Baru</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Perbarui kata sandi langsung tanpa perlu memasukkan sandi lama</p>
                        </div>
                    </div>
                </div>

                {{-- Direct Password Change Banner --}}
                <div class="mb-5 p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-200/70 text-indigo-900 text-xs flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span><strong>Bebas Masuk Sandi Baru:</strong> Masukkan password baru yang Anda inginkan dan konfirmasikan.</span>
                </div>

                <form method="POST" action="{{ route('user.profile.update-password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- New Password Input --}}
                    <div>
                        <label for="password" class="form-label font-bold text-slate-800">
                            Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <input :type="showNew ? 'text' : 'password'"
                                   id="password" name="password" required minlength="6" autocomplete="new-password"
                                   placeholder="Minimal 6 karakter"
                                   class="form-input pl-10 pr-12 text-sm font-medium @error('password') is-invalid @enderror">
                            
                            {{-- Show / Hide Toggle --}}
                            <button type="button" @click="showNew = !showNew"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showNew" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password Input --}}
                    <div>
                        <label for="password_confirmation" class="form-label font-bold text-slate-800">
                            Konfirmasi Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <input :type="showConfirm ? 'text' : 'password'"
                                   id="password_confirmation" name="password_confirmation" required minlength="6" autocomplete="new-password"
                                   placeholder="Ulangi password baru Anda"
                                   class="form-input pl-10 pr-12 text-sm font-medium">
                            
                            {{-- Show / Hide Toggle --}}
                            <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showConfirm" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Submit Password Button --}}
                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                                class="btn-primary w-full sm:w-auto px-6 py-2.5 text-xs font-bold rounded-xl shadow-sm transition active:scale-95 bg-indigo-600 hover:bg-indigo-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Perbarui Password Baru</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
