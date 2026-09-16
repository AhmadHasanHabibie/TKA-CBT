@extends('layouts.admin')

@section('title', 'Kelola Bank Soal — Admin TKA CBT')
@section('header-title', 'Kelola Bank Soal')

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    editModalOpen: false,
    editData: { id: '', title: '', category: '', description: '', is_active: true, updateUrl: '' },
    openEdit(bank, url) {
        this.editData = {
            id: bank.id,
            title: bank.title,
            category: bank.category || '',
            description: bank.description || '',
            is_active: !!bank.is_active,
            updateUrl: url
        };
        this.editModalOpen = true;
    },
    confirmDelete(id, title, count) {
        window.showConfirm({
            type: 'danger',
            title: 'Hapus Bank Soal',
            message: 'Menghapus &ldquo;' + title + '&rdquo; beserta ' + count + ' foto soal di dalamnya. Tindakan tidak dapat dibatalkan.',
            confirmText: 'Ya, Hapus Semua',
            action: () => this.$refs['deleteBank' + id].submit()
        });
    }
}">

    {{-- ── Page Header ──────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h2 class="page-title">Arsip &amp; Bank Soal</h2>
            <p class="page-subtitle">Kelola kumpulan bank soal gambar, unggah banyak foto sekaligus, dan atur ketersediaannya untuk siswa</p>
        </div>
        <button type="button" @click="createModalOpen = true" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Bank Soal Baru
        </button>
    </div>

    {{-- ── Filters & Search ─────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a href="{{ route('admin.question-banks.index') }}"
               class="px-3.5 py-2 rounded-xl font-bold transition-all duration-150 whitespace-nowrap
                      {{ !request('category') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200' }}">
                Semua Kategori
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('admin.question-banks.index', ['category' => $cat]) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all duration-150 whitespace-nowrap
                          {{ request('category') === $cat ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.question-banks.index') }}" class="flex items-center gap-2">
            @if (request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bank soal..."
                       class="form-input pl-9 text-sm w-56 sm:w-64">
            </div>
            <button type="submit" class="btn-primary btn-sm">Cari</button>
            @if (request('search') || request('category'))
                <a href="{{ route('admin.question-banks.index') }}" class="text-xs text-slate-400 hover:text-rose-600 font-semibold transition">Reset</a>
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
            <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6">
                Mulai buat bank soal baru untuk menampung koleksi foto soal dan diagram yang dapat dipelajari oleh siswa.
            </p>
            <button type="button" @click="createModalOpen = true" class="btn-primary btn-sm mx-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Bank Soal Sekarang
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($banks as $bank)
                <div class="card flex flex-col justify-between hover:shadow-md hover:border-slate-300/80 transition-all duration-200 overflow-hidden group">
                    <div class="p-6 space-y-3">
                        {{-- Category + toggle status --}}
                        <div class="flex items-center justify-between gap-2">
                            <span class="badge badge-indigo uppercase tracking-wide text-[10px]">
                                {{ $bank->category ?: 'Umum' }}
                            </span>
                            <form method="POST" action="{{ route('admin.question-banks.toggle', $bank) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" title="Klik untuk ubah status"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border transition-all duration-150
                                               {{ $bank->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $bank->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    {{ $bank->is_active ? 'Aktif' : 'Draft' }}
                                </button>
                            </form>
                        </div>

                        {{-- Title & Description --}}
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 leading-snug mb-1 line-clamp-2 group-hover:text-indigo-600 transition-colors duration-200">
                                {{ $bank->title }}
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                {{ $bank->description ?: 'Tidak ada deskripsi khusus.' }}
                            </p>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-600">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="font-bold text-slate-800">{{ $bank->items_count }}</span> Foto Soal
                            </div>
                            <span class="text-slate-200">•</span>
                            <span class="text-[11px] text-slate-400">{{ $bank->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center gap-2">
                        <a href="{{ route('admin.question-banks.items', $bank) }}"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 shadow-sm transition-all duration-150">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Kelola Foto ({{ $bank->items_count }})
                        </a>
                        <button type="button"
                                @click="openEdit({{ Js::from($bank) }}, '{{ route('admin.question-banks.update', $bank) }}')"
                                class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-amber-50 hover:border-amber-200 text-slate-500 hover:text-amber-600 transition" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <form x-ref="deleteBank{{ $bank->id }}" method="POST" action="{{ route('admin.question-banks.destroy', $bank) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    @click="confirmDelete({{ $bank->id }}, {{ Js::from($bank->title) }}, {{ $bank->items_count }})"
                                    class="p-2 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-400 hover:text-rose-600 transition cursor-pointer" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 pagination-wrapper">{{ $banks->links() }}</div>
    @endif

    {{-- ══════ MODAL: Buat Bank Soal ══════ --}}
    <div x-show="createModalOpen" x-cloak class="modal-backdrop" @keydown.escape.window="createModalOpen = false">
        <div class="modal-panel max-w-lg w-full scale-in" @click.away="createModalOpen = false">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Buat Bank Soal Baru</h3>
                </div>
                <button type="button" @click="createModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.question-banks.store') }}" id="create-bank-form" class="modal-body space-y-4">
                @csrf
                <div>
                    <label class="form-label">Nama Bank Soal <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Paket Soal UTBK TPS Penalaran Matematika"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Kategori / Mata Pelajaran</label>
                    <input type="text" name="category" list="cat-suggestions" placeholder="Contoh: Matematika, Bahasa Indonesia, TPS"
                           class="form-input">
                    <datalist id="cat-suggestions">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="form-label">Deskripsi / Petunjuk Belajar</label>
                    <textarea name="description" rows="3" placeholder="Tuliskan keterangan singkat, sumber soal, atau petunjuk penggunaan..."
                              class="form-input"></textarea>
                </div>
                <label class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                           class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                    <div>
                        <p class="text-xs font-bold text-slate-800">Publikasikan langsung</p>
                        <p class="text-[11px] text-slate-500">Dapat dilihat dan dipelajari oleh siswa</p>
                    </div>
                </label>
            </form>
            <div class="modal-footer">
                <button type="button" @click="createModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" form="create-bank-form" class="btn-primary btn-sm">Simpan &amp; Lanjut Unggah Foto</button>
            </div>
        </div>
    </div>

    {{-- ══════ MODAL: Edit Bank Soal ══════ --}}
    <div x-show="editModalOpen" x-cloak class="modal-backdrop" @keydown.escape.window="editModalOpen = false">
        <div class="modal-panel max-w-lg w-full scale-in" @click.away="editModalOpen = false">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Edit Bank Soal</h3>
                </div>
                <button type="button" @click="editModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" :action="editData.updateUrl" id="edit-bank-form" class="modal-body space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="form-label">Nama Bank Soal <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" x-model="editData.title" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Kategori / Mata Pelajaran</label>
                    <input type="text" name="category" x-model="editData.category" list="cat-suggestions" class="form-input">
                </div>
                <div>
                    <label class="form-label">Deskripsi / Petunjuk Belajar</label>
                    <textarea name="description" x-model="editData.description" rows="3" class="form-input"></textarea>
                </div>
                <label class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-model="editData.is_active"
                           class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                    <div>
                        <p class="text-xs font-bold text-slate-800">Publikasikan langsung</p>
                        <p class="text-[11px] text-slate-500">Dapat dilihat dan dipelajari oleh siswa</p>
                    </div>
                </label>
            </form>
            <div class="modal-footer">
                <button type="button" @click="editModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" form="edit-bank-form" class="btn-primary btn-sm">Simpan Perubahan</button>
            </div>
        </div>
    </div>

</div>
@endsection
