<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $questionBank->title }} — Dokumentasi {{ $questionBank->school_name ?: 'Foto Siswa' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-900 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white pb-16">

<div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 sm:pt-10 space-y-8"
     x-data="{
        viewerOpen: false,
        currentIndex: 0,
        zoomLevel: 1,
        touchStartX: 0,
        touchEndX: 0,
        items: {{ Js::from($questionBank->items->values()->map(function($item, $i) {
            $cleanTitle = ($item->title && !str_starts_with($item->title, 'WhatsApp Image'))
                ? $item->title
                : 'Foto Dokumentasi #' . ($i + 1);
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

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
        <a href="{{ route('home') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-400 hover:text-indigo-300 transition group">
            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Pencarian Dokumentasi</span>
        </a>

        @if($questionBank->school_name)
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span>{{ $questionBank->school_name }}</span>
        </span>
        @endif
    </div>

<div class="relative overflow-hidden rounded-3xl bg-slate-800/80 border border-slate-700/80 p-6 sm:p-8 backdrop-blur-xl">
        <div class="space-y-3 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-600/30 text-indigo-300 border border-indigo-500/30">
                    {{ $questionBank->category ?: 'Dokumentasi' }}
                </span>
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-700/60 text-slate-300 border border-slate-600/50">
                    {{ $questionBank->items->count() }} Foto
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-snug">
                {{ $questionBank->title }}
            </h1>

            @if($questionBank->description)
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                {{ $questionBank->description }}
            </p>
            @endif
        </div>
    </div>

@if($questionBank->items->isEmpty())
        <div class="rounded-3xl border border-slate-800 bg-slate-800/40 p-12 text-center space-y-3">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-800 flex items-center justify-center text-slate-500">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-300">Belum Ada Foto</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">Foto untuk album ini sedang diproses oleh admin sekolah.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($questionBank->items as $idx => $item)
                <div class="group relative rounded-2xl overflow-hidden bg-slate-800 border border-slate-700/60 shadow-lg hover:shadow-indigo-500/10 hover:border-indigo-500/40 transition-all duration-300 cursor-pointer"
                     @click="openViewer({{ $idx }})">
                    <div class="aspect-square w-full overflow-hidden bg-slate-950 relative">
                        <img src="{{ $item->image_url }}"
                             alt="{{ $item->title ?: 'Foto ' . ($idx + 1) }}"
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
                            <span class="text-xs font-semibold text-white truncate">
                                {{ $item->title ?: 'Foto #' . ($idx + 1) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

<div x-show="viewerOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/95 backdrop-blur-xl select-none"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

<div class="absolute top-0 inset-x-0 h-16 px-4 sm:px-6 flex items-center justify-between z-20 bg-gradient-to-b from-slate-950/90 to-transparent">
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-600/80 text-white"
                      x-text="(currentIndex + 1) + ' / ' + items.length"></span>
                <span class="text-xs sm:text-sm font-semibold text-slate-200 truncate max-w-[200px] sm:max-w-md"
                      x-text="currentItem.title"></span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                        @click="toggleZoom()"
                        class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition"
                        title="Perbesar / Perkecil">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                    </svg>
                </button>
                <a :href="currentItem.image_url"
                   download
                   target="_blank"
                   class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition"
                   title="Unduh Foto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </a>
                <button type="button"
                        @click="closeViewer()"
                        class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition"
                        title="Tutup (Esc)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

<div class="relative w-full h-full flex items-center justify-center p-4 sm:p-12 overflow-hidden"
             @touchstart="handleTouchStart($event)"
             @touchend="handleTouchEnd($event)">
            <template x-if="currentItem.image_url">
                <img :src="currentItem.image_url"
                     :alt="currentItem.title"
                     class="max-h-[85vh] max-w-[95vw] object-contain transition-transform duration-300 select-none"
                     :style="'transform: scale(' + zoomLevel + ')'"
                     @dblclick="toggleZoom()">
            </template>
        </div>

<button type="button"
                @click="prevSlide()"
                x-show="items.length > 1"
                class="absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-slate-900/70 hover:bg-indigo-600 text-white backdrop-blur-md transition shadow-xl z-20 border border-white/10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button type="button"
                @click="nextSlide()"
                x-show="items.length > 1"
                class="absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-slate-900/70 hover:bg-indigo-600 text-white backdrop-blur-md transition shadow-xl z-20 border border-white/10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

<div x-show="currentItem.notes"
             class="absolute bottom-4 inset-x-4 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 max-w-xl px-4 py-2 rounded-xl bg-slate-900/80 backdrop-blur-md border border-white/10 text-center text-xs text-slate-300 z-20">
            <span x-text="currentItem.notes"></span>
        </div>
    </div>

</div>

<x-client-telemetry />
</body>
</html>

