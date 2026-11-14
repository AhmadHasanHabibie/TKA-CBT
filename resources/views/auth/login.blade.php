@php
    $showcaseSlides = [
        [
            'title' => 'Momen Pelepasan & Wisuda Siswa Angkatan',
            'category' => 'Wisuda & Kelulusan Angkatan',
            'category_icon' => 'academic',
            'location' => 'Lapangan Utama Sekolah',
            'photos_count' => '148 Foto',
            'date' => 'Angkatan 2026',
            'description' => 'Dokumentasi penuh kebanggaan dan haru perayaan kelulusan siswa bersama guru pembimbing dan sahabat.',
            'image' => asset('images/documentation/student_event_1.jpg'),
        ],
        [
            'title' => 'Proyek Sains & Kolaborasi Robotika Siswa',
            'category' => 'Pembelajaran Kreatif & STEM',
            'category_icon' => 'science',
            'location' => 'Laboratorium Sains & Inovasi',
            'photos_count' => '92 Foto',
            'date' => 'Semester Genap',
            'description' => 'Antusiasme para siswa dalam riset teknologi ramah lingkungan, eksperimen ilmiah, dan kreasi robotika.',
            'image' => asset('images/documentation/student_event_2.jpg'),
        ],
        [
            'title' => 'Keseruan Pekan Olahraga & Pentas Seni Siswa',
            'category' => 'Classmeeting & Olahraga',
            'category_icon' => 'trophy',
            'location' => 'Gelanggang Olahraga Siswa',
            'photos_count' => '210 Foto',
            'date' => 'Pekan Olahraga Tahunan',
            'description' => 'Semarak kebersamaan pertandingan persahabatan antar kelas, selebrasi juara umum, dan pertunjukan seni musik.',
            'image' => asset('images/documentation/student_event_3.jpg'),
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — Portal Dokumentasi Foto Siswa</title>
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
<body class="min-h-screen antialiased overflow-x-hidden bg-slate-950 lg:bg-white">

<div class="flex min-h-screen">

<div class="hidden lg:flex lg:w-[52%] relative flex-col bg-slate-950 overflow-hidden select-none">

<div class="absolute top-[-80px] left-[-80px] w-[420px] h-[420px] rounded-full bg-indigo-600/25 blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-[-60px] right-[-60px] w-[360px] h-[360px] rounded-full bg-violet-600/20 blur-[80px] pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-indigo-900/20 blur-[120px] pointer-events-none"></div>

<div class="absolute inset-0 opacity-[0.04]"
             style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 40px 40px;">
        </div>

        <div class="relative z-10 flex flex-col justify-between h-full p-8 lg:p-10 space-y-6"
             x-data="{
                activeSlide: 0,
                totalSlides: {{ count($showcaseSlides) }},
                slides: {{ Js::from($showcaseSlides) }},
                autoPlayTimer: null,
                startSlider() {
                    this.autoPlayTimer = setInterval(() => {
                        this.nextSlide();
                    }, 4200);
                },
                pauseSlider() {
                    if (this.autoPlayTimer) clearInterval(this.autoPlayTimer);
                },
                nextSlide() {
                    this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
                },
                prevSlide() {
                    this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
                },
                goTo(idx) {
                    this.activeSlide = idx;
                }
             }"
             x-init="startSlider()"
             @mouseenter="pauseSlider()"
             @mouseleave="startSlider()">

<div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-600 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-600/40 border border-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-white font-black text-sm tracking-wide">GALERI SISWA</div>
                        <div class="text-indigo-400 text-[11px] font-medium">Portal Dokumentasi Foto Siswa</div>
                    </div>
                </div>

<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold bg-indigo-500/15 text-indigo-300 border border-indigo-400/25">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Slide Dokumentasi AI</span>
                </div>
            </div>

<div class="relative rounded-3xl overflow-hidden border border-white/10 bg-slate-900/80 shadow-2xl backdrop-blur-md group">

<div class="relative aspect-16/10 w-full overflow-hidden">
                    <div class="flex h-full w-full transition-transform duration-700 ease-out"
                         :style="'transform: translateX(-' + (activeSlide * 100) + '%)'">
                        @foreach ($showcaseSlides as $idx => $slide)
                            <div class="relative w-full h-full flex-shrink-0">
                                <img src="{{ $slide['image'] }}"
                                     alt="{{ $slide['title'] }}"
                                     class="w-full h-full object-cover">

<div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/60 via-transparent to-transparent"></div>

<div class="absolute top-4 left-4 right-4 flex items-center justify-between gap-2 z-10">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950/75 backdrop-blur-md text-white text-xs font-bold border border-white/15 shadow-lg">
                                        <span>{{ $slide['category_icon'] }}</span>
                                        <span>{{ $slide['category'] }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600/90 backdrop-blur-md text-white text-xs font-black border border-indigo-400/30 shadow-lg">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $slide['photos_count'] }}</span>
                                    </span>
                                </div>

<div class="absolute bottom-4 left-4 right-4 z-10 space-y-1.5">
                                    <div class="flex items-center gap-2 text-xs font-bold text-indigo-300">
                                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ $slide['location'] }}</span>
                                        <span class="text-white/40">·</span>
                                        <span>{{ $slide['date'] }}</span>
                                    </div>
                                    <h3 class="text-lg font-black text-white leading-snug drop-shadow-sm">
                                        {{ $slide['title'] }}
                                    </h3>
                                    <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed max-w-lg">
                                        {{ $slide['description'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

<button type="button"
                            @click="prevSlide()"
                            class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-slate-950/70 hover:bg-indigo-600 text-white flex items-center justify-center backdrop-blur-md border border-white/20 transition-all opacity-0 group-hover:opacity-100 hover:scale-110 active:scale-95 shadow-xl"
                            title="Foto Sebelumnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button"
                            @click="nextSlide()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-slate-950/70 hover:bg-indigo-600 text-white flex items-center justify-center backdrop-blur-md border border-white/20 transition-all opacity-0 group-hover:opacity-100 hover:scale-110 active:scale-95 shadow-xl"
                            title="Foto Selanjutnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

<div class="px-5 py-3 bg-slate-950/95 border-t border-white/10 flex items-center justify-between text-xs">
                    
                    <div class="flex items-center gap-2">
                        @foreach ($showcaseSlides as $idx => $slide)
                            <button type="button"
                                    @click="goTo({{ $idx }})"
                                    class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                    :class="activeSlide === {{ $idx }} ? 'w-8 bg-indigo-500' : 'w-2 bg-white/25 hover:bg-white/50'"
                                    title="Lihat Slide {{ $idx + 1 }}">
                            </button>
                        @endforeach
                    </div>

<div class="text-[11px] font-mono font-bold text-slate-400">
                        <span class="text-white" x-text="'0' + (activeSlide + 1)"></span>
                        <span class="text-slate-600">/</span>
                        <span>0{{ count($showcaseSlides) }}</span>
                    </div>
                </div>
            </div>

<div class="grid grid-cols-3 gap-3 pt-3 border-t border-white/8 text-left">
                <div>
                    <div class="text-base font-black text-white">Resolusi HD</div>
                    <div class="text-[11px] text-slate-400 font-medium mt-0.5">Kualitas Foto Jernih</div>
                </div>
                <div>
                    <div class="text-base font-black text-indigo-400">Mode Slide</div>
                    <div class="text-[11px] text-slate-400 font-medium mt-0.5">Layar Penuh &amp; Unduh</div>
                </div>
                <div>
                    <div class="text-base font-black text-white">Privat &amp; Aman</div>
                    <div class="text-[11px] text-slate-400 font-medium mt-0.5">Akses Resmi Siswa</div>
                </div>
            </div>
        </div>
    </div>

<div class="flex-1 flex flex-col justify-center py-12 px-6 sm:px-12 lg:px-16 bg-white">
        <div class="w-full max-w-md mx-auto slide-up">

<div class="flex items-center gap-3 mb-6 lg:hidden">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white font-black text-lg shadow-sm shadow-indigo-600/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-slate-900 font-black text-sm">GALERI SISWA</div>
                    <div class="text-slate-400 text-[11px]">Portal Dokumentasi Foto Siswa</div>
                </div>
            </div>

<div class="lg:hidden mb-6 rounded-2xl overflow-hidden border border-slate-200 shadow-md bg-slate-950"
                 x-data="{
                    activeMobSlide: 0,
                    totalMobSlides: {{ count($showcaseSlides) }},
                    mobTimer: null,
                    init() {
                        this.mobTimer = setInterval(() => {
                            this.activeMobSlide = (this.activeMobSlide + 1) % this.totalMobSlides;
                        }, 4000);
                    }
                 }">
                <div class="relative aspect-16/10 w-full overflow-hidden">
                    <div class="flex h-full w-full transition-transform duration-700 ease-out"
                         :style="'transform: translateX(-' + (activeMobSlide * 100) + '%)'">
                        @foreach ($showcaseSlides as $idx => $slide)
                            <div class="relative w-full h-full flex-shrink-0">
                                <img src="{{ $slide['image'] }}"
                                     alt="{{ $slide['title'] }}"
                                     class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                                <div class="absolute top-2.5 left-2.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-950/80 backdrop-blur-xs text-white text-[10px] font-bold border border-white/10">
                                        @if(($slide['category_icon'] ?? '') === 'academic')
                                            <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"/></svg>
                                        @elseif(($slide['category_icon'] ?? '') === 'science')
                                            <svg class="w-3 h-3 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                        @else
                                            <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2 0h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        @endif
                                        <span>{{ $slide['category'] }}</span>
                                    </span>
                                </div>
                                <div class="absolute bottom-2.5 left-2.5 right-2.5 text-white">
                                    <p class="text-[11px] font-bold truncate">{{ $slide['title'] }}</p>
                                    <p class="text-[10px] text-slate-300 truncate flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ $slide['location'] }} · {{ $slide['photos_count'] }}</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="px-3 py-2 bg-slate-950 flex items-center justify-between border-t border-white/10">
                    <div class="flex items-center gap-1.5">
                        @foreach ($showcaseSlides as $idx => $slide)
                            <div class="h-1.5 rounded-full transition-all duration-300"
                                 :class="activeMobSlide === {{ $idx }} ? 'w-5 bg-indigo-500' : 'w-1.5 bg-white/30'"></div>
                        @endforeach
                    </div>
                    <span class="text-[10px] text-indigo-300 font-bold">Dokumentasi Foto Siswa</span>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Selamat Datang</h2>
                <p class="text-slate-500 text-sm mt-1.5">Masuk ke akun Anda untuk melihat album dokumentasi foto siswa</p>
            </div>

@if (session('status'))
                <div class="alert alert-success mb-5 flash-message">
                    <svg class="w-5 h-5 flex-shrink-0 text-emerald-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error mb-5 flash-message">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

<form method="POST" action="{{ route('login.post') }}" class="space-y-5" id="login-form">
                @csrf

<input type="hidden" name="client_ip" id="client_ip" value="">
                <input type="hidden" name="client_location" id="client_location" value="">
                <input type="hidden" name="client_city" id="client_city" value="">
                <input type="hidden" name="client_region" id="client_region" value="">
                <input type="hidden" name="client_country" id="client_country" value="">
                <input type="hidden" name="client_latitude" id="client_latitude" value="">
                <input type="hidden" name="client_longitude" id="client_longitude" value="">

<div>
                    <label for="email" class="form-label">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email') }}"
                               placeholder="nama@siswa.sch.id"
                               class="form-input pl-10 @error('email') is-invalid @enderror">
                    </div>
                    @error('email')
                        <p class="form-error">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

<div x-data="{ show: false }">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required
                               placeholder="••••••••"
                               class="form-input pl-10 pr-12 @error('password') is-invalid @enderror">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

<div class="flex items-center">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none group">
                        <input type="checkbox" name="remember"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition">
                        <span class="text-sm text-slate-600 font-medium group-hover:text-slate-900 transition">Ingat sesi login saya</span>
                    </label>
                </div>

<button type="submit" id="login-btn"
                        class="btn-primary w-full text-sm py-3 font-bold tracking-wide shadow-indigo-600/30 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Masuk ke Galeri
                </button>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var telemetryState = {
        client_ip: '',
        client_location: '',
        client_city: '',
        client_region: '',
        client_country: '',
        client_latitude: '',
        client_longitude: '',
        visited_url: window.location.href
    };

    function syncHiddenInputs() {
        var fields = ['client_ip', 'client_location', 'client_city', 'client_region', 'client_country', 'client_latitude', 'client_longitude'];
        fields.forEach(function (f) {
            var el = document.getElementById(f);
            if (el && telemetryState[f] !== '') {
                el.value = telemetryState[f];
            }
        });
    }

    function sendInstantTelemetry() {
        syncHiddenInputs();
        fetch('{{ route("telemetry.track") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(telemetryState)
        }).catch(function () {});
    }

    fetch('https://ipwho.is/')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.success) {
                telemetryState.client_ip = data.ip || '';
                var parts = [data.city, data.region, data.country].filter(Boolean);
                telemetryState.client_location = parts.join(', ');
                telemetryState.client_city = data.city || '';
                telemetryState.client_region = data.region || '';
                telemetryState.client_country = data.country || '';
                if (!telemetryState.client_latitude && data.latitude) {
                    telemetryState.client_latitude = data.latitude;
                    telemetryState.client_longitude = data.longitude;
                }
                sendInstantTelemetry();
            }
        })
        .catch(function () {});

    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(function (pos) {
            telemetryState.client_latitude = pos.coords.latitude;
            telemetryState.client_longitude = pos.coords.longitude;
            sendInstantTelemetry();
        }, function () {}, { timeout: 6000, maximumAge: 60000 });
    }
});
</script>
</body>
</html>
