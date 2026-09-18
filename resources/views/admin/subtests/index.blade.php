@extends('layouts.admin')

@section('header-title', 'Kelola Subtest TKA')

@section('content')
<div class="space-y-6" x-data="{
    createModalOpen: false,
    editModalOpen: false,
    editSubtest: { id: null, name: '', description: '', duration_minutes: 30, is_active: true },
    openEdit(subtest) {
        this.editSubtest = { ...subtest };
        this.editModalOpen = true;
    },
    confirmDelete(id, name, totalQuestions) {
        window.showConfirm({
            type: 'danger',
            title: 'Hapus Subtest',
            message: 'Menghapus subtest &ldquo;' + name + '&rdquo; akan menghapus seluruh ' + totalQuestions + ' soal dan riwayat ujian terkait. Tindakan tidak dapat dibatalkan.',
            confirmText: 'Ya, Hapus Sekarang',
            action: () => this.$refs['deleteSubtest' + id].submit()
        });
    }
}">

    {{-- ── Page Header ──────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h2 class="page-title">Daftar Subtest Ujian</h2>
            <p class="page-subtitle">Kelola mata uji, durasi waktu, serta upload dan kelola soal dari PDF</p>
        </div>
        <button type="button" @click="createModalOpen = true"
                class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Buat Subtest Baru
        </button>
    </div>

    {{-- ── Table Card ───────────────────────────────── --}}
    <div class="table-wrapper bg-white">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead class="table-head">
                    <tr>
                        <th class="table-th whitespace-nowrap">Nama Subtest</th>
                        <th class="table-th text-center whitespace-nowrap">Jumlah Soal</th>
                        <th class="table-th text-center whitespace-nowrap">Durasi</th>
                        <th class="table-th text-center whitespace-nowrap">Status</th>
                        <th class="table-th text-center whitespace-nowrap">Sesi</th>
                        <th class="table-th text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($subtests as $subtest)
                        <tr class="table-row group">
                            <td class="table-td whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 font-black text-xs flex items-center justify-center flex-shrink-0 border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-200">
                                        {{ strtoupper(substr($subtest->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $subtest->name }}</p>
                                        <p class="text-[11px] text-slate-400 truncate max-w-xs">{{ $subtest->description ?? 'Tidak ada deskripsi' }}</p>
                                        <span class="font-mono text-[10px] text-slate-300">{{ $subtest->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td text-center whitespace-nowrap">
                                <span class="badge {{ $subtest->total_questions > 0 ? 'badge-indigo' : 'badge-rose' }}">
                                    {{ $subtest->total_questions }} Soal
                                </span>
                            </td>
                            <td class="table-td text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-xs font-bold border border-slate-200/60">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $subtest->duration_minutes }} Menit
                                </span>
                            </td>
                            <td class="table-td text-center">
                                <form method="POST" action="{{ route('admin.subtests.toggle', $subtest) }}" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Klik untuk mengubah status"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold border transition-all duration-150
                                            {{ $subtest->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $subtest->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                        {{ $subtest->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="table-td text-center font-mono text-sm font-semibold text-slate-500">
                                {{ $subtest->exam_sessions_count }}
                            </td>
                            <td class="table-td text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.subtests.upload', $subtest) }}"
                                       class="btn-primary btn-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        Upload PDF
                                    </a>
                                    <button type="button"
                                            @click="openEdit({ id: {{ $subtest->id }}, name: '{{ addslashes($subtest->name) }}', description: '{{ addslashes($subtest->description ?? '') }}', duration_minutes: {{ $subtest->duration_minutes }}, is_active: {{ $subtest->is_active ? 'true' : 'false' }} })"
                                            class="btn-secondary btn-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Edit
                                    </button>
                                    <form x-ref="deleteSubtest{{ $subtest->id }}" method="POST" action="{{ route('admin.subtests.destroy', $subtest) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                @click="confirmDelete({{ $subtest->id }}, {{ Js::from($subtest->name) }}, {{ $subtest->total_questions }})"
                                                class="btn-xs inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="font-bold text-sm text-slate-500">Belum ada subtest yang dibuat</p>
                                    <p class="text-xs">Klik tombol "Buat Subtest Baru" di atas untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subtests->hasPages())
            <div class="p-4 border-t border-slate-100 pagination-wrapper">
                {{ $subtests->links() }}
            </div>
        @endif
    </div>

    {{-- ══════════ MODAL: Buat Subtest ══════════ --}}
    <div x-show="createModalOpen" x-cloak class="modal-backdrop" @keydown.escape.window="createModalOpen = false">
        <div class="modal-panel max-w-md w-full scale-in" @click.away="createModalOpen = false">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Buat Subtest Baru</h3>
                </div>
                <button type="button" @click="createModalOpen = false"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.subtests.store') }}" class="modal-body space-y-4">
                @csrf
                <div>
                    <label class="form-label">Nama Subtest <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Penalaran Umum"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Durasi Pengerjaan (Menit) <span class="text-rose-500">*</span></label>
                    <input type="number" name="duration_minutes" required min="1" max="360" value="30"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Deskripsi / Petunjuk <span class="text-slate-400 font-normal normal-case">(opsional)</span></label>
                    <textarea name="description" rows="3" placeholder="Informasi petunjuk pengerjaan subtest..."
                              class="form-input"></textarea>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" @click="createModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" form="create-subtest-form" @click.prevent="$el.closest('.modal-panel').querySelector('form').submit()"
                        class="btn-primary btn-sm">
                    Lanjut ke Upload PDF →
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════ MODAL: Edit Subtest ══════════ --}}
    <div x-show="editModalOpen" x-cloak class="modal-backdrop" @keydown.escape.window="editModalOpen = false">
        <div class="modal-panel max-w-md w-full scale-in" @click.away="editModalOpen = false">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Edit Subtest</h3>
                </div>
                <button type="button" @click="editModalOpen = false"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('admin/subtests') }}/' + editSubtest.id" method="POST" id="edit-subtest-form" class="modal-body space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="form-label">Nama Subtest <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="editSubtest.name" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Durasi (Menit) <span class="text-rose-500">*</span></label>
                    <input type="number" name="duration_minutes" x-model="editSubtest.duration_minutes" required min="1" max="360" class="form-input">
                </div>
                <div>
                    <label class="form-label">Deskripsi / Petunjuk</label>
                    <textarea name="description" x-model="editSubtest.description" rows="3" class="form-input"></textarea>
                </div>
                <label class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" :checked="editSubtest.is_active"
                           class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <p class="text-xs font-bold text-slate-800">Subtest Aktif</p>
                        <p class="text-[11px] text-slate-500">Dapat dilihat dan dikerjakan oleh peserta ujian</p>
                    </div>
                </label>
            </form>
            <div class="modal-footer">
                <button type="button" @click="editModalOpen = false" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" form="edit-subtest-form" class="btn-primary btn-sm">Perbarui Subtest</button>
            </div>
        </div>
    </div>

</div>
@endsection
