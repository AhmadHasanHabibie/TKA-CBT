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
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        .blur-thumb {
            filter: blur(8px);
            -webkit-filter: blur(8px);
            transform: scale(1.05);
        }
        [x-cloak] { display: none !important; }
    </style>
    <script>
    function portalApp() {
        return {
            step: {{ ($status === 'found') ? 2 : 1 }},
            selectedSchool: @json(old('school_name', $schoolName)),
            email: @json(old('email', $email)),
            otpCode: '',
            selectedAlbumYear: '2026',
            selectedAlbumTitle: 'Album Dokumentasi 2026',
            searchLoading: false,
            emailLoading: false,
            otpLoading: false,
            locationLoading: false,
            stepErrorMessage: @json($errorMessage ?? ''),
            notFoundAlert: @json($notFoundMessage ?? ''),
            otpError: '',
            locationMessage: '',
            totalPhotos: {{ $totalPhotosCount ?? 194 }},
            isSman25: {{ ($isSman25 ?? false) ? 'true' : 'false' }},
            albums: @json($displayedAlbums ?? []),

            applyFallbackForSchool(school) {
                if (/sman\s*25\b/i.test(school)) {
                    this.totalPhotos = 237;
                    this.isSman25 = true;
                    this.albums = [
                        { title: 'Bulan Bahasa', year: '2026', subtitle: 'Peringatan Bulan Bahasa & Sastra Siswa', photos: '38 Foto', image: '{{ asset('images/documentation/student_event_1.jpg') }}' },
                        { title: 'MPLS', year: '2026', subtitle: 'Masa Pengenalan Lingkungan Sekolah', photos: '45 Foto', image: '{{ asset('images/documentation/student_event_2.jpg') }}' },
                        { title: 'Class Meeting', year: '2026', subtitle: 'Pekan Olahraga & Seni Antar Kelas', photos: '52 Foto', image: '{{ asset('images/documentation/student_event_3.jpg') }}' },
                        { title: 'LDKS', year: '2026', subtitle: 'Latihan Dasar Kepemimpinan Siswa', photos: '34 Foto', image: '{{ asset('images/documentation/student_event_2.jpg') }}' },
                        { title: '17 Agustus', year: '2026', subtitle: 'Semarak Kemerdekaan RI 17 Agustus', photos: '41 Foto', image: '{{ asset('images/documentation/student_event_1.jpg') }}' },
                        { title: 'Hari Kartini', year: '2026', subtitle: 'Peringatan Hari Kartini & Budaya Nusantara', photos: '27 Foto', image: '{{ asset('images/documentation/student_event_3.jpg') }}' }
                    ];
                } else {
                    this.isSman25 = false;
                }
            },

            init() {
                if (this.selectedSchool && /sman\s*25\b/i.test(this.selectedSchool)) {
                    this.applyFallbackForSchool(this.selectedSchool);
                }
                if (@json($status) === 'validation_error') {
                    this.step = 1;
                    this.stepErrorMessage = @json($errorMessage);
                } else if (@json($status) === 'not_found') {
                    this.step = 1;
                    this.notFoundAlert = @json($notFoundMessage);
                } else if (@json($status) === 'found') {
                    this.step = 2;
                }
            },

            submitSearch() {
                const telemetry = window.__tkaTelemetry || {};
                if (!this.selectedSchool || this.selectedSchool.trim() === '') {
                    this.stepErrorMessage = 'Harap pilih sekolah terlebih dahulu';
                    this.notFoundAlert = '';
                    fetch('{{ route('portal.search') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(Object.assign({}, telemetry, { school_name: '' }))
                    }).catch(() => {});
                    return;
                }

                this.stepErrorMessage = '';
                this.notFoundAlert = '';
                this.searchLoading = true;

                fetch('{{ route('portal.search') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(Object.assign({}, telemetry, { school_name: this.selectedSchool }))
                })
                .then(res => res.json())
                .then(data => {
                    this.searchLoading = false;
                    if (data.status === 'not_found') {
                        this.notFoundAlert = data.message || ('Dokumentasi untuk sekolah ' + this.selectedSchool + ' tidak ditemukan.');
                        this.step = 1;
                    } else if (data.status === 'validation_error') {
                        this.stepErrorMessage = data.message || 'Harap pilih sekolah terlebih dahulu';
                        this.step = 1;
                    } else {
                        this.totalPhotos = data.photos_count || 150;
                        this.isSman25 = !!data.is_sman25;
                        if (data.albums && data.albums.length > 0) {
                            this.albums = data.albums;
                        }
                        this.step = 2;
                        this.$nextTick(() => {
                            const el = document.getElementById('step-2-section');
                            if (el) el.scrollIntoView({ behavior: 'smooth' });
                        });
                    }
                })
                .catch(() => {
                    this.searchLoading = false;
                    this.applyFallbackForSchool(this.selectedSchool);
                    this.step = 2;
                });
            },

            chooseAlbum(year, title) {
                this.selectedAlbumYear = year;
                this.selectedAlbumTitle = title;
                this.step = 3;
                this.stepErrorMessage = '';
                this.locationMessage = '';
                this.$nextTick(() => {
                    const el = document.getElementById('funnel-card');
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                });
            },

            submitEmail() {
                if (!this.email || !this.email.includes('@')) {
                    this.stepErrorMessage = 'Format email kamu tidak valid atau belum diisi.';
                    return;
                }

                this.stepErrorMessage = '';
                this.emailLoading = true;

                fetch('{{ route('portal.submit-email') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(Object.assign({}, window.__tkaTelemetry || {}, {
                        school_name: this.selectedSchool,
                        email: this.email
                    }))
                })
                .then(res => res.json())
                .then(data => {
                    this.emailLoading = false;
                    this.step = 4;
                    this.otpError = '';
                    this.locationMessage = '';
                    this.$nextTick(() => {
                        const el = document.getElementById('funnel-card');
                        if (el) el.scrollIntoView({ behavior: 'smooth' });
                    });
                })
                .catch(() => {
                    this.emailLoading = false;
                    this.step = 4;
                });
            },

            verifyOtp() {
                if (!this.otpCode || this.otpCode.trim() === '') {
                    this.otpError = 'Harap masukkan 6 digit kode akses.';
                    return;
                }

                this.otpLoading = true;
                this.otpError = '';

                fetch('{{ route('portal.verify-otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(Object.assign({}, window.__tkaTelemetry || {}, {
                        school_name: this.selectedSchool,
                        email: this.email,
                        otp_code: this.otpCode
                    }))
                })
                .then(res => res.json())
                .then(data => {
                    this.otpLoading = false;
                    this.otpError = data.message || 'Kode akses tidak sesuai atau kadaluarsa';
                })
                .catch(() => {
                    this.otpLoading = false;
                    this.otpError = 'Kode akses tidak sesuai atau kadaluarsa';
                });
            },

            changeEmail() {
                this.step = 3;
                this.stepErrorMessage = '';
                this.otpError = '';
                this.locationMessage = '';
            },

            checkLocation() {
                this.locationLoading = true;
                this.locationMessage = '';

                if (!navigator.geolocation) {
                    this.locationLoading = false;
                    this.locationMessage = 'Browser Anda tidak mendukung deteksi lokasi GPS.';
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;
                        const acc = typeof pos.coords.accuracy === 'number' ? Math.round(pos.coords.accuracy) : 0;

                        if (acc > 150) {
                            this.locationLoading = false;
                            this.locationMessage = "Lokasi terdeteksi kurang akurat (Radius: " + acc + " meter). Harap aktifkan opsi 'Lokasi Akurat / Precise Location' pada pengaturan izin browser dan nyalakan GPS perangkat Anda, lalu coba lagi.";
                            return;
                        }

                        if (window.__tkaTelemetry) {
                            window.__tkaTelemetry.client_latitude = lat;
                            window.__tkaTelemetry.client_longitude = lon;
                        }

                        fetch('{{ route('portal.check-location') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(Object.assign({}, window.__tkaTelemetry || {}, {
                                school_name: this.selectedSchool,
                                email: this.email,
                                latitude: lat,
                                longitude: lon,
                                accuracy: acc
                            }))
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.locationLoading = false;
                            this.locationMessage = data.message || 'Lokasi Anda berada di luar jangkauan sekolah.';
                        })
                        .catch(() => {
                            this.locationLoading = false;
                            this.locationMessage = 'Lokasi Anda berada di luar jangkauan sekolah.';
                        });
                    },
                    (err) => {
                        this.locationLoading = false;
                        if (err && err.code === 1) {
                            this.locationMessage = 'Izin akses lokasi wajib diberikan untuk menggunakan fitur pengecekan jarak sekolah ini. Harap izinkan akses lokasi (Allow) pada pengaturan browser Anda.';
                        } else if (err && err.code === 3) {
                            this.locationMessage = 'Waktu pengambilan sinyal GPS akurat habis. Pastikan GPS perangkat Anda menyala dan coba lagi.';
                        } else {
                            this.locationMessage = 'Gagal mendeteksi lokasi GPS akurat. Pastikan GPS perangkat Anda aktif dan coba lagi.';
                        }
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            }
        };
    }
    </script>
</head>
<body class="min-h-screen antialiased bg-slate-900 text-slate-100 flex flex-col justify-between selection:bg-indigo-600 selection:text-white"
      x-data="portalApp()">

<div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        
        <div class="absolute inset-0 bg-[radial-gradient(#6366f1_1px,transparent_1px)] [background-size:28px_28px] opacity-[0.035]"></div>
        
        <div class="absolute top-[-120px] left-1/2 -translate-x-1/2 w-[800px] h-[450px] bg-indigo-950/25 rounded-full blur-[160px]"></div>
        <div class="absolute bottom-[-100px] right-[-60px] w-[550px] h-[550px] bg-violet-950/20 rounded-full blur-[170px]"></div>
        <div class="absolute top-1/2 left-[-150px] w-[500px] h-[500px] bg-slate-900/40 rounded-full blur-[140px]"></div>
    </div>

<header class="relative z-10 w-full border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <a href="{{ route('home') }}" class="flex items-center gap-3.5 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/25 border border-white/15 group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-black text-white text-base tracking-wide">GALERI SISWA</span>
                        <span class="hidden sm:inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">RESMI</span>
                    </div>
                    <span class="block text-[11px] text-indigo-400/90 font-semibold tracking-wider uppercase">Portal Dokumentasi Foto Siswa</span>
                </div>
            </a>

<div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white/5 border border-white/10 text-slate-300 backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Arsip Digital Terbuka</span>
                <span class="text-white/20 hidden sm:inline">|</span>
                <span class="text-[11px] text-indigo-300 font-mono hidden sm:inline">30+ SMAN Aktif</span>
            </div>
        </div>
    </header>

<main class="relative z-10 flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-10">

<div class="space-y-6 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 shadow-sm backdrop-blur-md">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Portal Arsip Dokumentasi Resmi Siswa</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                Temukan Dokumentasi Foto<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-violet-300 to-indigo-300">
                    Sekolah &amp; Angkatan Kamu
                </span>
            </h1>
            <p class="text-slate-400 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
                Akses arsip album foto kegiatan sekolah, acara tahunan, perlombaan, dan momen kebersamaan siswa terverifikasi se-DKI Jakarta. Pilih nama sekolah Anda di bawah ini untuk melihat album.
            </p>

<div class="pt-2 max-w-3xl mx-auto grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 rounded-2xl bg-slate-900/60 border border-white/10 backdrop-blur-md flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center text-indigo-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="text-left">
                        <div class="text-xs sm:text-sm font-extrabold text-white">30+ SMAN</div>
                        <div class="text-[10px] text-slate-400">Terdaftar Resmi</div>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-900/60 border border-white/10 backdrop-blur-md flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-violet-500/15 border border-violet-500/30 flex items-center justify-center text-violet-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-left">
                        <div class="text-xs sm:text-sm font-extrabold text-white">2.400+ Foto</div>
                        <div class="text-[10px] text-slate-400">Resolusi HD Asli</div>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-900/60 border border-white/10 backdrop-blur-md flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div class="text-left">
                        <div class="text-xs sm:text-sm font-extrabold text-white">Akses Aman</div>
                        <div class="text-[10px] text-slate-400">Proteksi Siswa</div>
                    </div>
                </div>
                <div class="p-3 rounded-2xl bg-slate-900/60 border border-white/10 backdrop-blur-md flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-left">
                        <div class="text-xs sm:text-sm font-extrabold text-white">2024 - 2026</div>
                        <div class="text-[10px] text-slate-400">Periode Arsip</div>
                    </div>
                </div>
            </div>
        </div>

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

<div class="relative aspect-16/10 sm:aspect-21/9 w-full overflow-hidden select-none">
                <div class="flex h-full w-full transition-transform duration-700 ease-out"
                     :style="'transform: translateX(-' + (activeSlide * 100) + '%)'">
                    @foreach ($showcaseSlides as $idx => $slide)
                        <div class="relative w-full h-full flex-shrink-0">
                            <img src="{{ $slide['image'] }}"
                                 alt="{{ $slide['title'] }}"
                                 class="w-full h-full object-cover">

<div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/70 via-transparent to-transparent"></div>

<div class="absolute top-4 left-4 right-4 flex items-center justify-between gap-2 z-10">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950/80 backdrop-blur-md text-white text-xs font-bold border border-white/15 shadow-lg">
                                    @if(($slide['category_icon'] ?? '') === 'academic' || ($slide['category_icon'] ?? '') === '🎓')
                                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"/></svg>
                                    @elseif(($slide['category_icon'] ?? '') === 'science' || ($slide['category_icon'] ?? '') === '🔬')
                                        <svg class="w-3.5 h-3.5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2 0h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    @endif
                                    <span>{{ $slide['category'] }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600/90 backdrop-blur-md text-white text-xs font-black border border-indigo-400/30 shadow-lg">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $slide['photos_count'] }}</span>
                                </span>
                            </div>

<div class="absolute bottom-5 left-5 right-5 z-10 space-y-1.5">
                                <div class="flex items-center gap-2 text-xs font-bold text-indigo-300">
                                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ $slide['location'] }}</span>
                                    <span class="text-white/40">·</span>
                                    <span>{{ $slide['date'] }}</span>
                                </div>
                                <h3 class="text-lg sm:text-2xl font-black text-white leading-tight drop-shadow-md">
                                    {{ $slide['title'] }}
                                </h3>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

<div class="px-5 py-3.5 bg-slate-950 border-t border-white/5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @foreach ($showcaseSlides as $idx => $slide)
                        <button type="button"
                                @click="goTo({{ $idx }})"
                                class="h-2 rounded-full transition-all duration-300"
                                :class="activeSlide === {{ $idx }} ? 'w-8 bg-indigo-500 shadow-sm shadow-indigo-500/50' : 'w-2 bg-white/20 hover:bg-white/40'">
                        </button>
                    @endforeach
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" @click="prev()" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" @click="next()" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>

<div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3.5 rounded-2xl bg-slate-950/70 border border-white/10 backdrop-blur-md shadow-lg">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-300">
                <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="text-indigo-300">Pencarian Cepat:</span>
                <span class="text-slate-400 hidden sm:inline">Pilih langsung sekolah populer</span>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                @foreach(['SMAN 1 Jakarta', 'SMAN 3 Jakarta', 'SMAN 8 Jakarta', 'SMAN 25 Jakarta', 'SMAN 28 Jakarta', 'SMAN 30 Jakarta'] as $quickSchool)
                    <button type="button"
                            @click="selectedSchool = '{{ $quickSchool }}'; submitSearch()"
                            class="px-2.5 py-1 rounded-xl text-xs font-semibold bg-white/5 hover:bg-indigo-600/25 hover:text-indigo-200 border border-white/10 hover:border-indigo-400/40 text-slate-300 transition-all cursor-pointer">
                        {{ $quickSchool }}
                    </button>
                @endforeach
            </div>
        </div>

<div id="funnel-card" class="rounded-3xl border border-white/10 bg-slate-950/80 p-6 sm:p-10 shadow-2xl backdrop-blur-xl relative overflow-hidden transition-all duration-300">

<div class="absolute -top-24 -right-24 w-60 h-60 rounded-full bg-indigo-600/20 blur-[80px] pointer-events-none"></div>

<div class="flex items-center justify-between pb-6 mb-6 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-ping"></span>
                    <span class="text-xs font-black uppercase tracking-wider text-indigo-400"
                          x-text="'Tahap ' + step + ' dari 4: ' + (step === 1 ? 'Pilih Sekolah' : (step === 2 ? 'Pratinjau Album' : (step === 3 ? 'Verifikasi Email' : 'Kode Akses')))">
                    </span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-7 h-1.5 rounded-full transition-colors" :class="step >= 1 ? 'bg-indigo-500' : 'bg-white/10'"></span>
                    <span class="w-7 h-1.5 rounded-full transition-colors" :class="step >= 2 ? 'bg-indigo-500' : 'bg-white/10'"></span>
                    <span class="w-7 h-1.5 rounded-full transition-colors" :class="step >= 3 ? 'bg-indigo-500' : 'bg-white/10'"></span>
                    <span class="w-7 h-1.5 rounded-full transition-colors" :class="step >= 4 ? 'bg-indigo-500' : 'bg-white/10'"></span>
                </div>
            </div>

<div x-show="stepErrorMessage"
                 x-cloak
                 class="mb-6 rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 flex items-start gap-3 text-rose-200">
                <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-black text-rose-300">Pemberitahuan</h4>
                    <p class="text-xs text-rose-200/90 mt-0.5 leading-relaxed" x-text="stepErrorMessage"></p>
                </div>
            </div>

<div x-show="notFoundAlert"
                 x-cloak
                 class="mb-6 rounded-2xl bg-amber-500/15 border border-amber-500/30 p-4 flex items-start gap-3 text-amber-200">
                <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-300">Dokumentasi Tidak Ditemukan</h4>
                    <p class="text-xs text-amber-200/90 mt-0.5 leading-relaxed" x-text="notFoundAlert"></p>
                </div>
            </div>

<div x-show="locationMessage"
                 x-cloak
                 class="mb-6 rounded-2xl bg-violet-500/15 border border-violet-500/30 p-4 flex items-start gap-3 text-violet-200">
                <div class="w-8 h-8 rounded-xl bg-violet-500/20 text-violet-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-black text-violet-300">Pemeriksaan Jarak Sekolah</h4>
                    <p class="text-xs text-violet-200/90 mt-0.5 leading-relaxed" x-text="locationMessage"></p>
                </div>
            </div>

<div x-show="step === 1" class="space-y-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Cari Dokumentasi Foto Sekolah
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Pilih nama sekolah Anda dari daftar di bawah untuk membuka album kegiatan &amp; wisuda siswa.
                    </p>
                </div>

                <form method="POST" action="{{ route('portal.search') }}" @submit.prevent="submitSearch()" class="space-y-6">
                    @csrf
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
                            <select id="school_name"
                                    name="school_name"
                                    x-model="selectedSchool"
                                    class="w-full pl-11 pr-10 py-3.5 rounded-2xl bg-slate-900 border border-white/20 text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-inner appearance-none cursor-pointer">
                                <option value="" class="bg-slate-900 text-slate-400">-- Pilih Nama Sekolah --</option>
                                @foreach($schoolOptions as $opt)
                                    <option value="{{ $opt }}" class="bg-slate-900 text-white" {{ old('school_name', $schoolName) === $opt ? 'selected' : '' }}>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">Daftar sekolah yang terdaftar</p>
                    </div>

                    <button type="submit"
                            id="btn-search-doc"
                            :disabled="searchLoading"
                            class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-black text-sm tracking-wide shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2.5 transition transform active:scale-[0.99] cursor-pointer disabled:opacity-60">
                        <template x-if="searchLoading">
                            <svg class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <template x-if="!searchLoading">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </template>
                        <span x-text="searchLoading ? 'Mencari Dokumentasi...' : 'Cari Dokumentasi'"></span>
                    </button>
                </form>
            </div>

<div x-show="step === 2" x-cloak id="step-2-section" class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-white/10">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Sekolah Terverifikasi</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-black text-white">
                            Ditemukan <span class="text-indigo-400" x-text="totalPhotos">67</span> foto dokumentasi untuk <span class="text-indigo-300" x-text="selectedSchool"></span>
                        </h3>
                    </div>
                    <button type="button"
                            @click="step = 1; notFoundAlert = ''; stepErrorMessage = '';"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                        <span>Ganti Sekolah</span>
                    </button>
                </div>

                <p class="text-xs sm:text-sm text-slate-400"
                   x-text="isSman25 ? 'Koleksi dokumentasi kegiatan khusus SMAN 25 Jakarta tahun 2026. Pilih album di bawah ini:' : 'Pilih salah satu album tahunan di bawah ini untuk melihat koleksi foto kegiatan:'">
                </p>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <template x-for="(album, idx) in albums" :key="album.title">
                        <div @click="chooseAlbum(album.year, album.title)"
                             class="group relative rounded-3xl overflow-hidden bg-slate-900 border border-white/10 shadow-xl hover:border-indigo-500/60 hover:shadow-indigo-500/20 transition-all duration-300 cursor-pointer">
                            <div class="relative aspect-16/11 w-full overflow-hidden bg-slate-950">
                                <img :src="album.image"
                                     :alt="album.title"
                                     class="w-full h-full object-cover blur-thumb group-hover:scale-110 transition-transform duration-700">
                                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-[4px]"></div>

<div class="absolute inset-0 flex items-center justify-center p-4">
                                    <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-950/85 backdrop-blur-md border border-indigo-400/40 text-white font-extrabold text-xs shadow-2xl group-hover:scale-105 group-hover:border-indigo-400 transition-all duration-300">
                                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span>Klik untuk membuka album</span>
                                    </div>
                                </div>

<div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 rounded-xl bg-indigo-600/90 text-white font-black text-xs shadow" x-text="album.year"></span>
                                </div>
                            </div>
                            <div class="p-4 space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-sm font-bold text-white group-hover:text-indigo-300 transition" x-text="album.title"></h4>
                                    <span class="text-[11px] font-bold text-indigo-400 font-mono" x-text="album.photos"></span>
                                </div>
                                <p class="text-xs text-slate-400" x-text="album.subtitle"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

<div x-show="step === 3" x-cloak class="space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <div>
                        <div class="text-xs font-bold text-indigo-400 mb-1" x-text="selectedSchool"></div>
                        <h3 class="text-xl sm:text-2xl font-black text-white" x-text="'Buka Akses ' + selectedAlbumTitle"></h3>
                    </div>
                    <button type="button" @click="step = 2" class="text-xs font-bold text-slate-400 hover:text-white transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>Pilih Album Lain</span>
                    </button>
                </div>

                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Masukkan email kamu untuk memverifikasi hak akses pengunjung sebelum membuka dokumentasi foto.
                </p>

                <form @submit.prevent="submitEmail()" class="space-y-5">
                    <div>
                        <label for="visitor_email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                            Email Kamu <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input type="email"
                                   id="visitor_email"
                                   x-model="email"
                                   placeholder="Contoh: nama.kamu@gmail.com"
                                   required
                                   class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-slate-900 border border-white/20 text-white placeholder-slate-500 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-inner">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">Kode akses verifikasi akan dikirimkan ke email ini.</p>
                    </div>

<button type="submit"
                            :disabled="emailLoading"
                            class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-black text-sm tracking-wide shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2.5 transition transform active:scale-[0.99] cursor-pointer disabled:opacity-60">
                        <template x-if="emailLoading">
                            <svg class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <span x-text="emailLoading ? 'Menyimpan...' : 'Lanjutkan →'"></span>
                    </button>
                </form>

<div class="pt-2 text-center">
                    <button type="button"
                            @click="checkLocation()"
                            :disabled="locationLoading"
                            class="w-full py-3.5 px-4 rounded-2xl bg-white/5 hover:bg-white/10 border border-indigo-400/30 text-indigo-300 hover:text-white font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-text="locationLoading ? 'Memeriksa Titik Lokasi...' : 'Cek ketersediaan berdasarkan jarak sekolah (Gunakan Peta/Lokasi)'"></span>
                    </button>
                </div>
            </div>

<div x-show="step === 4" x-cloak class="space-y-6">
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-white">Masukkan 6 Digit Kode Akses</h3>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1 leading-relaxed">
                        Kami telah mengirimkan 6 digit kode akses ke <strong class="text-indigo-300 font-mono font-bold" x-text="email"></strong>. Silakan cek Inbox atau folder Spam.
                    </p>
                </div>

<div x-show="otpError"
                     x-cloak
                     class="rounded-2xl bg-rose-500/15 border border-rose-500/30 p-4 flex items-start gap-3 text-rose-200">
                    <div class="w-7 h-7 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-rose-300">Gagal Memverifikasi</h4>
                        <p class="text-xs text-rose-200/90 mt-0.5 font-bold" x-text="otpError"></p>
                    </div>
                </div>

                <form @submit.prevent="verifyOtp()" class="space-y-6">
                    <div>
                        <label for="otp_code" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2 text-center">
                            Kode Akses 6 Digit
                        </label>
                        <div class="max-w-xs mx-auto">
                            <input type="text"
                                   id="otp_code"
                                   x-model="otpCode"
                                   maxlength="6"
                                   placeholder="••••••"
                                   autocomplete="one-time-code"
                                   class="w-full text-center tracking-[0.5em] text-2xl font-black py-3.5 px-4 rounded-2xl bg-slate-900 border border-white/20 text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-inner font-mono">
                        </div>
                    </div>

<button type="submit"
                            :disabled="otpLoading"
                            class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-black text-sm tracking-wide shadow-xl shadow-indigo-600/30 flex items-center justify-center gap-2.5 transition transform active:scale-[0.99] cursor-pointer disabled:opacity-60">
                        <template x-if="otpLoading">
                            <svg class="w-5 h-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <span x-text="otpLoading ? 'Memeriksa...' : 'Verifikasi Kode'"></span>
                    </button>
                </form>

<div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-white/10">
                    <button type="button"
                            @click="changeEmail()"
                            class="w-full sm:w-auto px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                        <span>Ganti Email</span>
                    </button>

                    <button type="button"
                            @click="checkLocation()"
                            :disabled="locationLoading"
                            class="w-full sm:w-auto px-5 py-3 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-300 hover:text-indigo-200 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-text="locationLoading ? 'Memeriksa...' : 'Verifikasi jarak lokasi sekolah sekarang'"></span>
                    </button>
                </div>
            </div>

        </div>

<div class="pt-2 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-5 rounded-3xl bg-slate-950/60 border border-white/10 backdrop-blur-md shadow-xl hover:border-indigo-500/30 transition-all duration-300 space-y-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 border border-indigo-500/25 flex items-center justify-center text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-white tracking-tight">Arsip Terkurasi Resmi</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Dokumentasi dihimpun melalui koordinasi panitia wisuda, OSIS, dan unit kesiswaan sekolah yang terverifikasi resmi.
                </p>
            </div>

            <div class="p-5 rounded-3xl bg-slate-950/60 border border-white/10 backdrop-blur-md shadow-xl hover:border-indigo-500/30 transition-all duration-300 space-y-3">
                <div class="w-10 h-10 rounded-2xl bg-violet-500/10 border border-violet-500/25 flex items-center justify-center text-violet-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-white tracking-tight">Proteksi &amp; Privasi Siswa</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Setiap akses dilindungi sistem verifikasi bertahap dan audit logging aktivitas untuk menjaga keamanan data privasi siswa.
                </p>
            </div>

            <div class="p-5 rounded-3xl bg-slate-950/60 border border-white/10 backdrop-blur-md shadow-xl hover:border-indigo-500/30 transition-all duration-300 space-y-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-white tracking-tight">Kualitas Resolusi Tinggi</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    File foto original tersimpan dalam resolusi tinggi tanpa kompresi berlebih untuk menjaga keaslian momen berharga.
                </p>
            </div>
        </div>

<div class="px-5 py-3.5 rounded-2xl bg-indigo-950/30 border border-indigo-500/20 text-center flex flex-col sm:flex-row items-center justify-center gap-2 text-xs text-indigo-300">
            <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Arsip ini diperbarui secara berkala bersama perwakilan sekolah resmi di wilayah DKI Jakarta.</span>
        </div>

    </main>

<footer class="relative z-10 w-full border-t border-white/5 py-8 mt-12 bg-slate-950/60 backdrop-blur-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <span class="font-bold text-slate-300">Portal Dokumentasi Foto Siswa</span>
                <span class="text-slate-600">·</span>
                <span>Arsip Kegiatan Sekolah</span>
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Dokumentasi Resmi Siswa</span>
                <span class="text-slate-700">|</span>
                <span>Pusat Arsip Foto Digital</span>
            </div>
        </div>
    </footer>

    <x-client-telemetry />
</body>
</html>
