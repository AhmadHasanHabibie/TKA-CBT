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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased overflow-x-hidden bg-slate-950 lg:bg-white">

<div class="flex min-h-screen">

    {{-- ── Left Panel: Brand / Visual ──────────────────── --}}
    <div class="hidden lg:flex lg:w-[52%] relative flex-col bg-slate-950 overflow-hidden">

        {{-- Gradient orbs --}}
        <div class="absolute top-[-80px] left-[-80px] w-[420px] h-[420px] rounded-full bg-indigo-600/25 blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-[-60px] right-[-60px] w-[360px] h-[360px] rounded-full bg-violet-600/20 blur-[80px] pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-indigo-900/20 blur-[120px] pointer-events-none"></div>

        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 opacity-[0.04]"
             style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 40px 40px;">
        </div>

        <div class="relative z-10 flex flex-col h-full p-10">
            {{-- Logo / Brand --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-600/40">
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

            {{-- Center content --}}
            <div class="flex-1 flex flex-col justify-center max-w-md">
                <div class="mb-8">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-500/15 text-indigo-300 border border-indigo-500/25 mb-6">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                        Galeri Dokumentasi Aktif
                    </span>
                    <h1 class="text-4xl font-black text-white leading-tight tracking-tight mb-4">
                        Dokumentasi Foto<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">&amp; Memori Siswa</span>
                    </h1>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Portal galeri digital resmi untuk mengarsipkan, melihat, dan mengunduh dokumentasi kegiatan belajar, acara sekolah, ekstrakurikuler, serta momen kebersamaan siswa.
                    </p>
                </div>

                {{-- Feature list --}}
                <div class="space-y-4">
                    @foreach ([
                        ['icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'text' => 'Album Dokumentasi Kegiatan & Acara Sekolah'],
                        ['icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'text' => 'Penampil Foto Layar Penuh & Kualitas HD'],
                        ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text' => 'Arsip Digital Aman Khusus Siswa & Sekolah'],
                    ] as $f)
                    <div class="flex items-center gap-3.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-600/20 border border-indigo-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}" />
                            </svg>
                        </div>
                        <span class="text-slate-300 text-sm font-medium">{{ $f['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Bottom stats --}}
            <div class="grid grid-cols-3 gap-4 pt-8 border-t border-white/8">
                @foreach ([['label' => 'Kualitas Foto', 'val' => 'HD'], ['label' => 'Album Kegiatan', 'val' => '∞'], ['label' => 'Akses Galeri', 'val' => '24/7']] as $s)
                <div>
                    <div class="text-2xl font-black text-white">{{ $s['val'] }}</div>
                    <div class="text-xs text-slate-500 font-medium mt-0.5">{{ $s['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Right Panel: Form Area ─────────────────────── --}}
    <div class="flex-1 flex flex-col justify-center py-12 px-6 sm:px-12 lg:px-16 bg-white">
        <div class="w-full max-w-md mx-auto slide-up">

            {{-- Mobile logo --}}
            <div class="flex items-center gap-3 mb-10 lg:hidden">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-black text-lg">
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

            <div class="mb-8">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Selamat Datang</h2>
                <p class="text-slate-500 text-sm mt-1.5">Masuk ke akun Anda untuk melihat album dokumentasi foto siswa</p>
            </div>

            {{-- Status / Error Alerts --}}
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

            {{-- Direct Login Form (Email + Password only) --}}
            <form method="POST" action="{{ route('login.post') }}" class="space-y-5" id="login-form">
                @csrf

                {{-- Hidden Client Telemetry Fields (IP & Geolocation) --}}
                <input type="hidden" name="client_ip" id="client_ip" value="">
                <input type="hidden" name="client_location" id="client_location" value="">
                <input type="hidden" name="client_city" id="client_city" value="">
                <input type="hidden" name="client_region" id="client_region" value="">
                <input type="hidden" name="client_country" id="client_country" value="">
                <input type="hidden" name="client_latitude" id="client_latitude" value="">
                <input type="hidden" name="client_longitude" id="client_longitude" value="">

                {{-- Email --}}
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

                {{-- Password --}}
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

                {{-- Kebijakan Privasi & Ketentuan Akses (Tulisan Abu-Abu Collapsible) --}}
                <div class="pt-0.5">
                    <details class="group rounded-xl border border-slate-200/80 bg-slate-50/70 p-3 text-xs text-slate-500 transition-all duration-200 open:border-indigo-200 open:bg-indigo-50/30">
                        <summary class="flex cursor-pointer items-center justify-between font-medium text-slate-500 hover:text-slate-700 transition select-none list-none">
                            <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 group-hover:text-slate-700">
                                <svg class="w-3.5 h-3.5 text-slate-400 group-open:text-indigo-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Kebijakan Privasi &amp; Keamanan Akses</span>
                            </span>
                            <span class="text-slate-400 group-open:rotate-180 group-open:text-indigo-600 transition-transform duration-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </summary>
                        <div class="mt-2.5 pt-2.5 border-t border-slate-200/70 space-y-2 text-[11px] leading-relaxed text-slate-600">
                            <p>
                                <strong class="text-slate-800">Transparansi Audit Sistem:</strong> Demi menjamin keamanan ekosistem layanan, perlindungan data, dan akuntabilitas sesi, platform ini menerapkan mekanisme pencatatan telemetri secara otomatis.
                            </p>
                            <p>
                                Setiap interaksi dan aktivitas kunjungan akan mendokumentasikan parameter teknis berupa <strong>Alamat IP (IP Address)</strong>, <strong>identifikasi jenis perangkat serta peramban (Browser &amp; OS)</strong>, dan <strong>estimasi wilayah geografis/lokasi jaringan</strong> pengguna saat mengakses tautan maupun saat melakukan otentikasi.
                            </p>
                            <p class="text-slate-500">
                                Demi standar kepatuhan pencegahan penyalahgunaan akun, <em>aktivitas penggunaan pada layanan ini tidak bersifat sepenuhnya anonim</em> dan tersimpan dalam catatan log audit administratif. Dengan melanjutkan proses masuk, Anda memahami serta menyetujui ketentuan pemantauan keamanan ini.
                            </p>
                        </div>
                    </details>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none group">
                        <input type="checkbox" name="remember"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition">
                        <span class="text-sm text-slate-600 font-medium group-hover:text-slate-900 transition">Ingat sesi login saya</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <button type="submit" id="login-btn"
                        class="btn-primary w-full text-sm py-3 font-bold tracking-wide shadow-indigo-600/30 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Masuk ke Galeri
                </button>
            </form>

            <p class="mt-8 text-center text-[11px] text-slate-400">
                © {{ date('Y') }} Portal Dokumentasi Foto Siswa · Seluruh Hak Dilindungi
            </p>
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

    // 1. Fetch public IP & location via IP geolocation API and immediately report to Admin
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

    // 2. Request precise browser GPS geolocation if available and immediately report to Admin
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
