@extends('layouts.admin')

@section('title', 'Profil Administrator')
@section('header-title', 'Pengaturan Akun Administrator')

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- Breadcrumb / Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight">Profil Administrator</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola identitas akun dan perbarui kata sandi secara langsung</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
            Superadmin
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Card 1: Ganti Nama Administrator --}}
        <div class="card p-6 sm:p-7 space-y-5 border border-slate-200/80 shadow-sm slide-up">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900">Nama & Identitas</h3>
                    <p class="text-xs text-slate-400">Ubah nama akun administrator</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="form-label font-bold text-slate-800">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="form-input text-sm @error('name') is-invalid @enderror">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label font-bold text-slate-800">Email Administrator</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="form-input text-sm bg-slate-50 text-slate-500 cursor-not-allowed">
                    <p class="form-hint">Email login administrator bersifat permanen.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full py-2.5 text-xs font-bold rounded-xl active:scale-95">
                        Simpan Nama
                    </button>
                </div>
            </form>
        </div>

        {{-- Card 2: Ganti Password Administrator --}}
        <div class="card p-6 sm:p-7 space-y-5 border border-slate-200/80 shadow-sm slide-up"
             x-data="{ showNew: false, showConfirm: false }">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900">Ganti Password</h3>
                    <p class="text-xs text-slate-400">Langsung ganti tanpa password lama</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.update-password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="admin_password" class="form-label font-bold text-slate-800">Password Baru</label>
                    <div class="relative">
                        <input :type="showNew ? 'text' : 'password'" id="admin_password" name="password" required minlength="6"
                               placeholder="Minimal 6 karakter"
                               class="form-input pr-10 text-sm @error('password') is-invalid @enderror">
                        <button type="button" @click="showNew = !showNew"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showNew" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="admin_password_confirmation" class="form-label font-bold text-slate-800">Konfirmasi Password Baru</label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" id="admin_password_confirmation" name="password_confirmation" required minlength="6"
                               placeholder="Ulangi password baru"
                               class="form-input pr-10 text-sm">
                        <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showConfirm" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full py-2.5 text-xs font-bold rounded-xl active:scale-95">
                        Perbarui Password
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
