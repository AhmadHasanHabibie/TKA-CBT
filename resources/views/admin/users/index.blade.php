@extends('layouts.admin')

@section('header-title', 'Kelola Data Siswa & Pengguna')

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    editModalOpen: false,
    editUser: { id: null, name: '', email: '', role: 'user' },
    openEdit(user) {
        this.editUser = { ...user };
        this.editModalOpen = true;
    },
    confirmDelete(id, name, email) {
        window.showConfirm({
            type: 'danger',
            title: 'Hapus Akun Siswa / Pengguna',
            message: 'Akun &ldquo;' + name + '&rdquo; (' + email + ') akan dihapus permanen dari sistem galeri.',
            confirmText: 'Ya, Hapus Akun',
            action: () => this.$refs['deleteUser' + id].submit()
        });
    }
}">

    {{-- ── Page Header ──────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h2 class="page-title">Daftar Siswa &amp; Pengelola</h2>
            <p class="page-subtitle">Kelola akun siswa dan administrator yang memiliki akses ke Portal Dokumentasi Foto Siswa</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email siswa..."
                       class="form-input pl-9 text-sm w-56 sm:w-64">
            </form>
            <button type="button" @click="createModalOpen = true" class="btn-primary btn-sm flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Akun Siswa
            </button>
        </div>
    </div>

    {{-- ── Table Card ───────────────────────────────── --}}
    <div class="table-wrapper bg-white">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th whitespace-nowrap">Nama Siswa / Pengguna</th>
                        <th class="table-th text-center whitespace-nowrap">Peran</th>
                        <th class="table-th whitespace-nowrap">Lokasi Login</th>
                        <th class="table-th whitespace-nowrap">IP Address</th>
                        <th class="table-th whitespace-nowrap">Browser &amp; OS</th>
                        <th class="table-th whitespace-nowrap">Login Terakhir</th>
                        <th class="table-th text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="table-row">
                            <td class="table-td whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs flex-shrink-0
                                        {{ $user->role === 'admin' ? 'bg-gradient-to-br from-purple-500 to-indigo-600 text-white' : 'bg-gradient-to-br from-indigo-100 to-slate-200 text-indigo-700' }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-slate-900">{{ $user->name }}</p>
                                        <p class="font-mono text-[11px] text-slate-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td text-center whitespace-nowrap">
                                @if ($user->role === 'admin')
                                    <span class="badge badge-purple">
                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1l2.928 5.93 6.544.95-4.736 4.615 1.117 6.51L10 15.966l-5.853 3.079 1.117-6.51L.528 7.88l6.544-.95L10 1z" clip-rule="evenodd"/></svg>
                                        Admin
                                    </span>
                                @else
                                    <span class="badge badge-indigo">
                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                        Siswa
                                    </span>
                                @endif
                            </td>
                            <td class="table-td">
                                @if ($user->last_login_location)
                                    <div class="flex items-start gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-rose-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <div>
                                            <span class="text-xs font-bold text-slate-800">{{ $user->last_login_location }}</span>
                                            @if ($user->last_login_latitude !== null && $user->last_login_longitude !== null)
                                                <div>
                                                    <a href="https://www.google.com/maps?q={{ $user->last_login_latitude }},{{ $user->last_login_longitude }}"
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="text-[10px] font-bold text-indigo-600 hover:underline">
                                                        Lihat Peta ({{ number_format($user->last_login_latitude, 3) }}, {{ number_format($user->last_login_longitude, 3) }})
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum pernah login</span>
                                @endif
                            </td>
                            <td class="table-td whitespace-nowrap">
                                @if ($user->last_login_ip)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 font-mono text-xs font-bold text-slate-700">
                                        {{ $user->last_login_ip }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="table-td whitespace-nowrap">
                                @if ($user->last_login_browser)
                                    <div class="text-xs font-bold text-slate-800">{{ $user->last_login_browser }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $user->last_login_os ?: '—' }}</div>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="table-td text-xs text-slate-500 font-mono whitespace-nowrap">
                                @if ($user->last_login_at)
                                    <div class="font-semibold text-slate-700">{{ $user->last_login_at->format('d M Y, H:i') }}</div>
                                    <div class="text-[10px] text-slate-400 font-sans">{{ $user->last_login_at->diffForHumans() }}</div>
                                @else
                                    <span class="text-slate-400 font-sans">Terdaftar {{ $user->created_at->format('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="table-td text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            @click="openEdit({ id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ $user->email }}', role: '{{ $user->role }}' })"
                                            class="btn-secondary btn-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Edit
                                    </button>
                                    @if ($user->id !== Auth::id())
                                        <form x-ref="deleteUser{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    @click="confirmDelete({{ $user->id }}, {{ Js::from($user->name) }}, {{ Js::from($user->email) }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 text-[11px] font-bold transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <p class="font-bold text-sm text-slate-500">Tidak ada pengguna ditemukan</p>
                                    <p class="text-xs">Coba ubah kata kunci pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-4 border-t border-slate-100 pagination-wrapper">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- ══════════ MODAL: Tambah Pengguna ══════════ --}}
    <div x-show="createModalOpen" x-cloak class="modal-backdrop" @keydown.escape.window="createModalOpen = false">
        <div class="modal-panel max-w-md w-full scale-in" @click.away="createModalOpen = false">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Tambah Akun Siswa / Pengguna</h3>
                </div>
                <button type="button" @click="createModalOpen = false"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" id="create-user-form" class="modal-body space-y-4">
                @csrf
                <div>
                    <label class="form-label">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="budi@siswa.sch.id" class="form-input">
                </div>
                <div>
                    <label class="form-label">Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="form-input">
                </div>
                <div>
                    <label class="form-label">Peran (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" required class="form-input bg-white">
                        <option value="user" selected>Siswa</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" @click="createModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" form="create-user-form" class="btn-primary btn-sm">Simpan Akun</button>
            </div>
        </div>
    </div>

    {{-- ══════════ MODAL: Edit Pengguna ══════════ --}}
    <div x-show="editModalOpen" x-cloak class="modal-backdrop" @keydown.escape.window="editModalOpen = false">
        <div class="modal-panel max-w-md w-full scale-in" @click.away="editModalOpen = false">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Edit Data Akun</h3>
                </div>
                <button type="button" @click="editModalOpen = false"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('admin/users') }}/' + editUser.id" method="POST" id="edit-user-form" class="modal-body space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="editUser.name" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" x-model="editUser.email" required class="form-input">
                </div>
                <div>
                    <label class="form-label">
                        Password Baru
                        <span class="text-slate-400 font-normal normal-case text-[11px]">(kosongkan jika tidak diubah)</span>
                    </label>
                    <input type="password" name="password" minlength="6" placeholder="••••••••" class="form-input">
                </div>
                <div>
                    <label class="form-label">Peran (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" x-model="editUser.role" required class="form-input bg-white">
                        <option value="user">Siswa</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" @click="editModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" form="edit-user-form" class="btn-primary btn-sm">Perbarui Akun</button>
            </div>
        </div>
    </div>

</div>
@endsection
