@extends('layouts.user')

@section('title', $questionBank->title . ' — Bank Soal TKA CBT')

@section('content')
<div class="space-y-8" x-data="{
    viewerOpen: false,
    currentIndex: 0,
    items: {{ Js::from($questionBank->items->map(fn($item) => [
        'id' => $item->id,
        'title' => $item->title ?: 'Foto Soal #' . $item->order,
        'image_url' => $item->image_url,
        'notes' => $item->notes,
        'order' => $item->order
    ])) }},
    openViewer(index) {
        this.currentIndex = index;
        this.viewerOpen = true;
    },
    nextSlide() {
        if (this.currentIndex < this.items.length - 1) {
            this.currentIndex++;
        } else {
            this.currentIndex = 0; // loop back to first
        }
    },
    prevSlide() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
        } else {
            this.currentIndex = this.items.length - 1; // loop to last
        }
    },
    get currentItem() {
        return this.items[this.currentIndex] || {};
    }
}"
@keydown.left.window="if(viewerOpen) prevSlide()"
@keydown.right.window="if(viewerOpen) nextSlide()"
@keydown.escape.window="viewerOpen = false">

    <!-- Top Navigation & Title Bar -->
    <div class="card p-6 sm:p-8 space-y-4">
        <a href="{{ route('user.question-banks.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-indigo-600 transition group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Semua Bank Soal
            <span class="text-slate-300 mx-1">/</span>
            <span class="font-bold text-slate-700">{{ $questionBank->category ?: 'Mata Uji Umum' }}</span>
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">
                    {{ $questionBank->title }}
                </h1>
                <p class="text-sm text-slate-500 mt-1.5 leading-relaxed max-w-3xl">
                    {{ $questionBank->description ?: 'Koleksi foto soal dan diagram latihan untuk membantu persiapan tes Anda.' }}
                </p>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <span class="badge badge-indigo text-xs px-3 py-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $questionBank->items->count() }} Foto Soal
                </span>

                @if ($questionBank->items->isNotEmpty())
                    <button type="button" @click="openViewer(0)" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Mulai Belajar (Slide Mode)
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Questions Photo Gallery -->
    @if ($questionBank->items->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-sm">
            <p class="text-xs text-slate-500 font-medium">
                Belum ada foto soal di dalam bank soal ini.
            </p>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-bold text-slate-700">Daftar Foto Soal & Pembahasan</span>
                <span>Klik foto mana pun untuk membuka mode penampil layar penuh.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach ($questionBank->items as $idx => $item)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden cursor-pointer group"
                        @click="openViewer({{ $idx }})">
                        
                        <!-- Thumbnail Image Container -->
                        <div class="relative aspect-4/3 bg-slate-50 border-b border-slate-100 flex items-center justify-center p-2 overflow-hidden">
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}"
                                class="max-h-full max-w-full object-contain rounded-lg transition duration-200 group-hover:scale-105"
                                loading="lazy">

                            <!-- Order Badge -->
                            <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-lg bg-slate-900/80 text-white text-[11px] font-bold backdrop-blur-xs font-mono">
                                #{{ $idx + 1 }}
                            </div>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-indigo-900/30 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <div class="px-3 py-1.5 rounded-xl bg-white text-indigo-700 text-xs font-bold shadow-lg flex items-center gap-1.5 transform translate-y-1 group-hover:translate-y-0 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                    <span>Lihat Soal</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info / Notes -->
                        <div class="p-4 space-y-2">
                            <h3 class="text-xs font-bold text-slate-900 truncate group-hover:text-indigo-600 transition">
                                {{ $item->title ?: 'Foto Soal #' . ($idx + 1) }}
                            </h3>
                            @if ($item->notes)
                                <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed bg-slate-50 p-2 rounded-lg border border-slate-100">
                                    {{ $item->notes }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Interactive Fullscreen Slide Viewer Modal -->
    <div x-show="viewerOpen" style="display: none;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-6 bg-slate-950/90 backdrop-blur-md"
        @click.self="viewerOpen = false">

        <div class="relative w-full max-w-6xl max-h-[96vh] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col">
            <!-- Modal Header Bar -->
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-xl bg-indigo-600 text-white text-xs font-bold font-mono">
                        <span x-text="currentIndex + 1"></span> / <span x-text="items.length"></span>
                    </span>
                    <h3 class="text-sm font-bold truncate max-w-md" x-text="currentItem.title"></h3>
                </div>

                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline-block text-[11px] text-slate-400 mr-2">
                        Gunakan tombol panah ◄ ► untuk navigasi
                    </span>
                    <button type="button" @click="viewerOpen = false"
                        class="w-8 h-8 rounded-full bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white flex items-center justify-center text-sm font-bold transition">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Slide Main Image & Navigation Arrows -->
            <div class="relative flex-1 bg-slate-950 flex items-center justify-center p-4 min-h-[400px] max-h-[68vh] select-none">
                <!-- Previous Button -->
                <button type="button" @click="prevSlide()"
                    class="absolute left-4 z-10 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-indigo-600 text-white flex items-center justify-center shadow-lg backdrop-blur-xs transition hover:scale-105 cursor-pointer"
                    title="Foto Sebelumnya (Panah Kiri)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <!-- Current Photo -->
                <img :src="currentItem.image_url" :alt="currentItem.title"
                    class="max-h-full max-w-full object-contain rounded-xl shadow-2xl transition duration-150">

                <!-- Next Button -->
                <button type="button" @click="nextSlide()"
                    class="absolute right-4 z-10 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-indigo-600 text-white flex items-center justify-center shadow-lg backdrop-blur-xs transition hover:scale-105 cursor-pointer"
                    title="Foto Selanjutnya (Panah Kanan)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <!-- Bottom Notes / Explanation Bar -->
            <div class="p-5 bg-white border-t border-slate-100 flex-shrink-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex-1">
                        <template x-if="currentItem.notes">
                            <div class="p-3 rounded-xl bg-indigo-50/80 border border-indigo-100 text-xs text-slate-800 leading-relaxed">
                                <span class="font-bold text-indigo-900 block mb-0.5">Catatan / Pembahasan:</span>
                                <p x-text="currentItem.notes" class="whitespace-pre-line"></p>
                            </div>
                        </template>
                        <template x-if="!currentItem.notes">
                            <p class="text-xs text-slate-400 italic">
                                Tidak ada catatan tambahan untuk soal ini.
                            </p>
                        </template>
                    </div>

                    <!-- Quick navigation buttons -->
                    <div class="flex items-center gap-2 self-end sm:self-center">
                        <button type="button" @click="prevSlide()"
                            class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">
                            ◄ Sebelumnya
                        </button>
                        <button type="button" @click="nextSlide()"
                            class="px-4 py-1.5 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-xs font-semibold transition">
                            Selanjutnya ►
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
