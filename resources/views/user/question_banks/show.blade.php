@extends('layouts.user')

@section('title', $questionBank->title . ' — Bank Soal TKA CBT')

@section('content')
<div class="space-y-6 sm:space-y-8" x-data="{
    viewerOpen: false,
    currentIndex: 0,
    zoomLevel: 1,
    touchStartX: 0,
    touchEndX: 0,
    items: {{ Js::from($questionBank->items->values()->map(function($item, $i) {
        $cleanTitle = ($item->title && !str_starts_with($item->title, 'WhatsApp Image'))
            ? $item->title
            : 'Foto Soal #' . ($i + 1);
        return [
            'id' => $item->id,
            'title' => $cleanTitle,
            'image_url' => $item->image_url,
            'notes' => $item->notes,
            'order' => $item->order
        ];
    })) }},
    openViewer(index) {
        this.currentIndex = index;
        this.zoomLevel = 1;
        this.viewerOpen = true;
        document.body.classList.add('overflow-hidden');
    },
    closeViewer() {
        this.viewerOpen = false;
        this.zoomLevel = 1;
        document.body.classList.remove('overflow-hidden');
    },
    nextSlide() {
        this.zoomLevel = 1;
        if (this.currentIndex < this.items.length - 1) {
            this.currentIndex++;
        } else {
            this.currentIndex = 0;
        }
    },
    prevSlide() {
        this.zoomLevel = 1;
        if (this.currentIndex > 0) {
            this.currentIndex--;
        } else {
            this.currentIndex = this.items.length - 1;
        }
    },
    toggleZoom() {
        this.zoomLevel = this.zoomLevel === 1 ? 1.75 : 1;
    },
    handleTouchStart(e) {
        this.touchStartX = e.changedTouches[0].screenX;
    },
    handleTouchEnd(e) {
        this.touchEndX = e.changedTouches[0].screenX;
        const diff = this.touchStartX - this.touchEndX;
        if (Math.abs(diff) > 45 && this.zoomLevel === 1) {
            if (diff > 0) {
                this.nextSlide();
            } else {
                this.prevSlide();
            }
        }
    },
    get currentItem() {
        return this.items[this.currentIndex] || {};
    }
}"
@keydown.left.window="if(viewerOpen && zoomLevel === 1) prevSlide()"
@keydown.right.window="if(viewerOpen && zoomLevel === 1) nextSlide()"
@keydown.escape.window="if(viewerOpen) closeViewer()">

    <!-- Top Navigation & Title Bar -->
    <div class="card p-5 sm:p-7 space-y-4">
        <a href="{{ route('user.question-banks.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-indigo-600 transition group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Semua Bank Soal</span>
            <span class="text-slate-300">/</span>
            <span class="font-bold text-slate-700">{{ $questionBank->category ?: 'Mata Uji Umum' }}</span>
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">
                        {{ $questionBank->title }}
                    </h1>
                    <span class="badge badge-indigo text-[11px] px-2.5 py-0.5">
                        {{ $questionBank->items->count() }} Foto Soal
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-3xl">
                    {{ $questionBank->description ?: 'Koleksi foto soal dan diagram latihan untuk membantu persiapan tes Anda.' }}
                </p>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                @if ($questionBank->items->isNotEmpty())
                    <button type="button"
                            @click="openViewer(0)"
                            class="w-full sm:w-auto btn-primary btn-sm py-2.5 px-4 font-bold shadow-sm shadow-indigo-600/30 flex items-center justify-center gap-2 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Mulai Belajar (Mode Slide)</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Questions Photo Gallery -->
    @if ($questionBank->items->isEmpty())
        <div class="card p-12 text-center shadow-sm">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800 mb-1">Belum Ada Foto Soal</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                Belum ada arsip foto soal di dalam bank soal ini.
            </p>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex items-center justify-between text-xs text-slate-500 px-1">
                <span class="font-bold text-slate-700">Daftar Foto Soal &amp; Pembahasan</span>
                <span class="text-[11px] hidden sm:inline">Klik foto untuk membuka penampil layar penuh</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach ($questionBank->items as $idx => $item)
                    @php
                        $displayTitle = ($item->title && !str_starts_with($item->title, 'WhatsApp Image'))
                            ? $item->title
                            : 'Foto Soal #' . ($idx + 1);
                    @endphp
                    <div class="card-hover overflow-hidden flex flex-col justify-between cursor-pointer group border border-slate-200/90"
                         @click="openViewer({{ $idx }})">

                        <!-- Thumbnail Image Container -->
                        <div class="relative aspect-4/3 bg-slate-900 flex items-center justify-center p-2 overflow-hidden">
                            <img src="{{ $item->image_url }}"
                                 alt="{{ $displayTitle }}"
                                 class="max-h-full max-w-full object-contain rounded-lg transition duration-200 group-hover:scale-105"
                                 loading="lazy">

                            <!-- Order Badge -->
                            <div class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-lg bg-slate-950/85 text-white text-[11px] font-black font-mono shadow-md border border-white/10 backdrop-blur-xs">
                                #{{ $idx + 1 }}
                            </div>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-indigo-950/40 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <div class="px-3.5 py-2 rounded-xl bg-white text-indigo-700 text-xs font-bold shadow-xl flex items-center gap-1.5 transform translate-y-1 group-hover:translate-y-0 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                    </svg>
                                    <span>Buka Soal</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info / Notes -->
                        <div class="p-4 space-y-2 bg-white flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 truncate group-hover:text-indigo-600 transition">
                                    {{ $displayTitle }}
                                </h3>
                                @if ($item->notes)
                                    <p class="mt-1 text-[11px] text-slate-600 line-clamp-2 leading-relaxed bg-indigo-50/60 p-2 rounded-lg border border-indigo-100/60 font-medium">
                                        {{ $item->notes }}
                                    </p>
                                @endif
                            </div>
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-indigo-600 font-bold group-hover:translate-x-0.5 transition-transform">
                                <span>Lihat Detail</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ═══════════════ FULLSCREEN IMMERSIVE SLIDE VIEWER MODAL ═══════════════ -->
    <div x-show="viewerOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex flex-col bg-slate-950 w-full h-full h-[100dvh] select-none overflow-hidden"
         @touchstart="handleTouchStart($event)"
         @touchend="handleTouchEnd($event)">

        <!-- Viewer Top Toolbar Header -->
        <header class="h-16 px-4 sm:px-6 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 flex items-center justify-between gap-3 flex-shrink-0 z-20">
            <!-- Left: Counter & Title -->
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-mono font-bold text-xs tracking-wider shadow-sm flex-shrink-0">
                    <span x-text="currentIndex + 1"></span> / <span x-text="items.length"></span>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-xs sm:text-sm font-bold text-white truncate" x-text="currentItem.title"></h2>
                    <p class="text-[10px] text-slate-400 truncate hidden sm:block">{{ $questionBank->title }}</p>
                </div>
            </div>

            <!-- Right: Actions & Guaranteed Close Button -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <!-- Zoom Toggle -->
                <button type="button"
                        @click="toggleZoom()"
                        class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700/80 text-xs font-semibold flex items-center gap-1.5 transition active:scale-95"
                        :title="zoomLevel === 1 ? 'Perbesar Foto (Zoom)' : 'Kembalikan Ukuran Normal'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="zoomLevel === 1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                        <path x-show="zoomLevel !== 1" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                    </svg>
                    <span class="hidden sm:inline" x-text="zoomLevel === 1 ? 'Perbesar' : 'Normal'"></span>
                </button>

                <!-- Close Button: ALWAYS VISIBLE AND PROMINENT -->
                <button type="button"
                        @click="closeViewer()"
                        class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white border border-slate-700/80 flex items-center justify-center font-black text-sm transition-all duration-150 active:scale-95 shadow-sm"
                        aria-label="Tutup Penampil Soal"
                        title="Tutup (Esc)">
                    ✕
                </button>
            </div>
        </header>

        <!-- Main Image Display Canvas (Unobstructed, NO buttons blocking text) -->
        <div class="relative flex-1 bg-slate-950 flex items-center justify-center overflow-auto p-2 sm:p-4 min-h-0">
            <!-- Desktop Left Arrow (Floating safely at the screen edge) -->
            <button type="button"
                    @click="prevSlide()"
                    class="hidden md:flex absolute left-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 rounded-2xl bg-slate-900/80 hover:bg-indigo-600 text-white border border-slate-700/80 items-center justify-center shadow-2xl backdrop-blur-md transition-all duration-150 hover:scale-110 active:scale-95 cursor-pointer"
                    title="Soal Sebelumnya (Panah Kiri)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <!-- Image Container with Zoom State -->
            <div class="relative max-h-full max-w-full flex items-center justify-center transition-transform duration-200"
                 :class="{ 'scale-150 cursor-grab': zoomLevel > 1 }">
                <img :src="currentItem.image_url"
                     :alt="currentItem.title"
                     @dblclick="toggleZoom()"
                     class="max-h-[calc(100dvh-170px)] sm:max-h-[calc(100dvh-190px)] max-w-full object-contain rounded-xl shadow-2xl transition-all duration-150"
                     loading="eager">
            </div>

            <!-- Desktop Right Arrow (Floating safely at the screen edge) -->
            <button type="button"
                    @click="nextSlide()"
                    class="hidden md:flex absolute right-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 rounded-2xl bg-slate-900/80 hover:bg-indigo-600 text-white border border-slate-700/80 items-center justify-center shadow-2xl backdrop-blur-md transition-all duration-150 hover:scale-110 active:scale-95 cursor-pointer"
                    title="Soal Selanjutnya (Panah Kanan)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- Bottom Controls & Notes Bar (Cohesive Dark Theme, High Contrast) -->
        <footer class="bg-slate-900/95 backdrop-blur-md border-t border-slate-800 px-4 sm:px-6 py-3 sm:py-4 flex-shrink-0 z-20 space-y-2.5">
            <!-- Notes / Explanation Section -->
            <template x-if="currentItem.notes">
                <div class="rounded-xl bg-slate-800/90 border border-slate-700 p-3 sm:p-3.5 text-xs text-slate-100 max-h-24 overflow-y-auto">
                    <div class="flex items-center gap-1.5 font-bold text-amber-400 text-[11px] mb-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <span>Catatan / Kunci Pembahasan:</span>
                    </div>
                    <p x-text="currentItem.notes" class="whitespace-pre-line leading-relaxed text-slate-200 text-xs"></p>
                </div>
            </template>

            <!-- Bottom Navigation Bar -->
            <div class="flex items-center justify-between gap-2">
                <!-- Prev Button -->
                <button type="button"
                        @click="prevSlide()"
                        class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 active:bg-slate-600 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold transition active:scale-95 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span>Sebelumnya</span>
                </button>

                <!-- Center Counter & Swipe Hint -->
                <div class="text-center">
                    <span class="text-xs font-bold text-slate-300">
                        Soal <span x-text="currentIndex + 1" class="text-indigo-400 font-mono"></span> dari <span x-text="items.length" class="font-mono"></span>
                    </span>
                    <span class="block text-[10px] text-slate-500 sm:hidden">Geser layar ◄ ►</span>
                </div>

                <!-- Next Button -->
                <button type="button"
                        @click="nextSlide()"
                        class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition active:scale-95">
                    <span>Selanjutnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </footer>
    </div>

</div>
@endsection

