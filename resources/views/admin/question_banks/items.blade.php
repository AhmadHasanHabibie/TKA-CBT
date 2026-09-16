@extends('layouts.admin')

@section('title', 'Kelola Foto Bank Soal — ' . $questionBank->title)
@section('header-title', 'Kelola Foto Bank Soal')

@section('content')
<div class="space-y-6" x-data="{
    lightboxOpen: false,
    lightboxImage: '',
    lightboxTitle: '',
    lightboxNotes: '',
    openLightbox(img, title, notes) {
        this.lightboxImage = img;
        this.lightboxTitle = title || 'Foto Soal';
        this.lightboxNotes = notes || '';
        this.lightboxOpen = true;
    },
    editItemModal: false,
    editItem: {
        id: '',
        title: '',
        notes: '',
        order: 0,
        updateUrl: ''
    },
    openEditItem(item, url) {
        this.editItem = {
            id: item.id,
            title: item.title || '',
            notes: item.notes || '',
            order: item.order || 0,
            updateUrl: url
        };
        this.editItemModal = true;
    },
    confirmDeleteItem(id, title) {
        window.showConfirm({
            type: 'warning',
            title: 'Hapus Foto Soal',
            message: 'Foto soal &ldquo;' + title + '&rdquo; akan dihapus permanen dari bank soal ini.',
            confirmText: 'Ya, Hapus Foto',
            action: () => this.$refs['deleteItem' + id].submit()
        });
    },
    selectedFiles: [],
    filePreviews: [],
    handleFiles(e) {
        const files = Array.from(e.target.files);
        this.selectedFiles = files;
        this.filePreviews = [];
        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                this.filePreviews.push({
                    name: file.name,
                    size: (file.size / 1024).toFixed(0) + ' KB',
                    url: URL.createObjectURL(file)
                });
            }
        });
    },
    clearSelectedFiles() {
        this.selectedFiles = [];
        this.filePreviews = [];
        if (this.$refs.multiInput) {
            this.$refs.multiInput.value = '';
        }
    }
}">

    <!-- Top Breadcrumb & Actions -->
    <div class="card p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.question-banks.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-indigo-600 transition mb-2 group">
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Bank Soal
            </a>
            <h2 class="page-title flex items-center gap-2.5">
                <span>{{ $questionBank->title }}</span>
                <span class="{{ $questionBank->is_active ? 'badge badge-emerald' : 'badge badge-slate' }}">
                    {{ $questionBank->is_active ? 'Aktif' : 'Draft' }}
                </span>
            </h2>
            <p class="page-subtitle">
                Total <strong class="text-slate-700">{{ $questionBank->items->count() }} foto soal</strong> tersimpan dalam bank ini.
            </p>
        </div>

        <a href="{{ route('user.question-banks.show', $questionBank) }}" target="_blank"
           class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
            Lihat Tampilan Siswa
        </a>
    </div>

    <!-- Batch Multi-Photo Upload Dropzone Section -->
    <div class="drop-zone bg-indigo-50/30 border-indigo-200">
        <form method="POST" action="{{ route('admin.question-banks.items.upload', $questionBank) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="text-center max-w-lg mx-auto">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 mb-1">Unggah Banyak Foto Soal Sekaligus</h3>
                <p class="text-xs text-slate-500 mb-4">
                    Pilih atau tarik hingga puluhan file foto sekaligus (JPG, PNG, WebP, GIF maks 15MB per foto).
                </p>

                <!-- Hidden Input & Trigger Button -->
                <input type="file" x-ref="multiInput" name="images[]" multiple accept="image/*"
                    @change="handleFiles($event)" class="hidden" id="multi_images_input">

                <div class="flex items-center justify-center gap-3 mt-2">
                    <label for="multi_images_input" class="btn-primary btn-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Pilih Foto dari Komputer
                    </label>
                    <template x-if="filePreviews.length > 0">
                        <button type="button" @click="clearSelectedFiles()"
                            class="btn-secondary btn-sm cursor-pointer">
                            Reset Pilihan
                        </button>
                    </template>
                </div>
            </div>

            <!-- Client-side Selected Files Preview Grid -->
            <template x-if="filePreviews.length > 0">
                <div class="pt-4 border-t border-indigo-100 mt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800">
                            Pratinjau File Terpilih (<span x-text="filePreviews.length"></span> foto):
                        </span>
                        <button type="submit" class="btn-success btn-sm cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Unggah Semua Foto Sekarang
                        </button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 max-h-60 overflow-y-auto p-2 bg-white rounded-xl border border-slate-200">
                        <template x-for="(f, idx) in filePreviews" :key="idx">
                            <div class="relative group rounded-lg overflow-hidden border border-slate-200 bg-slate-50 p-1 text-center">
                                <img :src="f.url" class="h-20 w-full object-cover rounded-md mb-1">
                                <p class="text-[10px] font-semibold text-slate-700 truncate px-1" x-text="f.name"></p>
                                <span class="text-[9px] text-slate-400" x-text="f.size"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </form>
    </div>

    <!-- Existing Photos Gallery in Bank Soal -->
    @if ($questionBank->items->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
            <p class="text-xs text-slate-500 font-medium">
                Belum ada foto yang diunggah ke bank soal ini. Gunakan kotak unggah di atas untuk menambahkan foto soal.
            </p>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">
                    Daftar Foto Soal ({{ $questionBank->items->count() }})
                </h3>
                <span class="text-xs text-slate-500 font-medium">
                    Klik foto untuk memperbesar atau tombol edit untuk menambah catatan pembahasan.
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($questionBank->items as $item)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden group">
                        <!-- Image Container with Hover Zoom Action -->
                        <div class="relative aspect-4/3 bg-slate-50 border-b border-slate-100 overflow-hidden cursor-pointer flex items-center justify-center p-2"
                            @click="openLightbox('{{ $item->image_url }}', '{{ addslashes($item->title) }}', '{{ addslashes($item->notes ?? '') }}')">
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}"
                                class="max-h-full max-w-full object-contain rounded-lg transition duration-200 group-hover:scale-105"
                                loading="lazy">

                            <!-- Order Badge -->
                            <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-lg bg-slate-900/80 text-white text-[11px] font-bold backdrop-blur-xs font-mono">
                                #{{ $item->order }}
                            </div>

                            <!-- Click to Zoom Pill -->
                            <div class="absolute bottom-2.5 right-2.5 px-2 py-1 rounded-lg bg-indigo-600 text-white text-[10px] font-bold shadow-xs opacity-0 group-hover:opacity-100 transition flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                <span>Perbesar</span>
                            </div>
                        </div>

                        <!-- Card Info & Notes -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 truncate mb-1" title="{{ $item->title }}">
                                    {{ $item->title ?: 'Foto Soal #' . $item->order }}
                                </h4>
                                @if ($item->notes)
                                    <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed bg-slate-50 p-2 rounded-lg border border-slate-100">
                                        {{ $item->notes }}
                                    </p>
                                @else
                                    <p class="text-[11px] text-slate-400 italic">
                                        Belum ada catatan pembahasan.
                                    </p>
                                @endif
                            </div>

                            <!-- Card Action Buttons -->
                            <div class="flex items-center justify-between gap-2 pt-3 mt-3 border-t border-slate-100">
                                <button type="button"
                                    @click="openEditItem({{ Js::from($item) }}, '{{ route('admin.question-banks.items.update', [$questionBank, $item]) }}')"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    <span>Edit Info</span>
                                </button>

                                <form x-ref="deleteItem{{ $item->id }}" method="POST" action="{{ route('admin.question-banks.items.destroy', [$questionBank, $item]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            @click="confirmDeleteItem({{ $item->id }}, {{ Js::from($item->title ?: 'Foto #' . $item->order) }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus Foto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Lightbox Zoom Modal -->
    <div x-show="lightboxOpen" style="display: none;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
        @keydown.escape.window="lightboxOpen = false"
        @click.self="lightboxOpen = false">
        <div class="relative max-w-5xl w-full max-h-[92vh] bg-white rounded-3xl p-4 sm:p-6 shadow-2xl overflow-y-auto flex flex-col">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900" x-text="lightboxTitle"></h3>
                <button type="button" @click="lightboxOpen = false"
                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center font-bold text-sm">
                    ✕
                </button>
            </div>

            <div class="flex-1 flex items-center justify-center bg-slate-50 rounded-2xl p-2 mb-3 min-h-[350px]">
                <img :src="lightboxImage" :alt="lightboxTitle" class="max-h-[65vh] max-w-full rounded-xl object-contain shadow-xs">
            </div>

            <template x-if="lightboxNotes">
                <div class="p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-100 text-xs text-slate-800 leading-relaxed">
                    <span class="font-bold text-indigo-900 block mb-1">Catatan / Pembahasan:</span>
                    <p x-text="lightboxNotes" class="whitespace-pre-line"></p>
                </div>
            </template>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div x-show="editItemModal" x-cloak class="modal-backdrop" @keydown.escape.window="editItemModal = false">
        <div class="modal-panel max-w-md w-full scale-in" @click.away="editItemModal = false">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Edit Data Foto Soal</h3>
                </div>
                <button type="button" @click="editItemModal = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" :action="editItem.updateUrl" id="edit-item-form" class="modal-body space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="form-label">Judul / Label Foto</label>
                    <input type="text" name="title" x-model="editItem.title" placeholder="Misal: Soal 1, Diagram Matriks" class="form-input">
                </div>
                <div>
                    <label class="form-label">Nomor Urut</label>
                    <input type="number" name="order" x-model="editItem.order" min="0" class="form-input">
                </div>
                <div>
                    <label class="form-label">Catatan / Pembahasan <span class="text-slate-400 font-normal normal-case text-[11px]">(opsional)</span></label>
                    <textarea name="notes" x-model="editItem.notes" rows="4"
                              placeholder="Tuliskan kunci jawaban, rumus, atau petunjuk penyelesaian..."
                              class="form-input"></textarea>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" @click="editItemModal = false" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" form="edit-item-form" class="btn-primary btn-sm">Simpan Perubahan</button>
            </div>
        </div>
    </div>

</div>
@endsection
