<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'Galeri Dokumentasi Foto Siswa')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col antialiased bg-slate-50" x-data="{ mobileNavOpen: false }">

<header class="bg-white/90 backdrop-blur-sm border-b border-slate-200/80 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

<a href="{{ route('user.dashboard') }}" class="flex items-center gap-2.5 flex-shrink-0 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center font-black text-sm shadow-sm shadow-indigo-600/30 group-hover:shadow-indigo-600/50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="leading-tight hidden sm:block">
                    <span class="block font-black text-slate-900 text-[13px] tracking-tight">GALERI SISWA</span>
                    <span class="block text-[10px] text-slate-400 font-semibold uppercase tracking-widest">Dokumentasi Foto</span>
                </div>
            </a>

<nav class="hidden sm:flex items-center gap-1">
                <a href="{{ route('user.dashboard') }}"
                   class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150
                          {{ request()->routeIs('user.dashboard') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Beranda Galeri
                </a>
                <a href="{{ route('user.question-banks.index') }}"
                   class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150
                          {{ request()->routeIs('user.question-banks.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Album Foto
                </a>
                <a href="{{ route('user.profile.edit') }}"
                   class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150
                          {{ request()->routeIs('user.profile.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profil Siswa
                </a>
            </nav>

<div class="flex items-center gap-3">
                <a href="{{ route('user.profile.edit') }}"
                   class="hidden md:flex items-center gap-2.5 p-1 rounded-2xl hover:bg-slate-100 transition group"
                   title="Buka Pengaturan Profil">
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-900 leading-none group-hover:text-indigo-600 transition">{{ Auth::user()->name ?? 'Siswa' }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ Auth::user()->email ?? '' }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center font-black text-xs border-2 border-white shadow-sm group-hover:scale-105 transition">
                        {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-rose-50 hover:border-rose-200 text-slate-600 hover:text-rose-600 text-xs font-bold transition-all duration-150 shadow-sm active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>

<button @click="mobileNavOpen = !mobileNavOpen"
                        class="sm:hidden p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileNavOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileNavOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

<div x-show="mobileNavOpen" x-cloak class="sm:hidden border-t border-slate-100 px-4 pb-3 pt-2 space-y-1 bg-white slide-up">
            <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('user.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }} transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Beranda Galeri
            </a>
            <a href="{{ route('user.question-banks.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('user.question-banks.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }} transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Album Foto
            </a>
            <a href="{{ route('user.profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('user.profile.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }} transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profil Siswa
            </a>
            <a href="{{ route('user.profile.edit') }}" class="pt-2 border-t border-slate-100 flex items-center gap-2.5 px-3 hover:bg-slate-50 rounded-xl transition">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center font-black text-xs">
                    {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->name ?? 'Siswa' }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </header>

<main class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-6 pb-16 slide-up">
        @yield('content')
    </main>

<footer class="border-t border-slate-200/80 bg-white/60 backdrop-blur-sm py-4">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between text-[11px] text-slate-400">
            <span>© {{ date('Y') }} Portal Dokumentasi Foto Siswa</span>
            <span class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Galeri Online
            </span>
        </div>
    </footer>

    <x-flash-toast />
    <x-confirm-modal />
    <x-client-telemetry />

    @stack('scripts')
</body>
</html>
