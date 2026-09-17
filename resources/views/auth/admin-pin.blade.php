<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi PIN Admin — TKA CBT System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col items-center justify-center p-4 sm:p-6 relative overflow-hidden font-sans select-none">

    {{-- ── Background Ambient Lights ───────────────────── --}}
    <div class="absolute top-[-100px] left-[-100px] w-[500px] h-[500px] rounded-full bg-indigo-600/20 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-100px] right-[-100px] w-[500px] h-[500px] rounded-full bg-violet-600/20 blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-indigo-950/40 blur-[140px] pointer-events-none"></div>

    {{-- Grid pattern overlay --}}
    <div class="absolute inset-0 opacity-[0.035] pointer-events-none"
         style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 40px 40px;">
    </div>

    {{-- ── Main PIN Card ───────────────────────────────── --}}
    <div class="relative z-10 w-full max-w-md bg-slate-900/85 backdrop-blur-2xl border border-slate-800/90 rounded-3xl p-7 sm:p-9 shadow-2xl shadow-black/80 slide-up"
         x-data="{
             digits: ['', '', '', '', '', ''],
             showPin: false,
             submitting: false,
             get fullPin() {
                 return this.digits.join('');
             },
             init() {
                 this.$nextTick(() => {
                     if (this.$refs.digit0) this.$refs.digit0.focus();
                 });
             },
             handleInput(index, event) {
                 const input = event.target;
                 const val = input.value.replace(/\D/g, '');
                 if (val.length > 0) {
                     this.digits[index] = val.slice(-1);
                     input.value = this.digits[index];
                     if (index < 5) {
                         this.$refs['digit' + (index + 1)].focus();
                     } else if (this.fullPin.length === 6) {
                         this.submitForm();
                     }
                 } else {
                     this.digits[index] = '';
                 }
             },
             handleKeydown(index, event) {
                 if (event.key === 'Backspace') {
                     if (!this.digits[index] && index > 0) {
                         this.digits[index - 1] = '';
                         this.$refs['digit' + (index - 1)].focus();
                     } else {
                         this.digits[index] = '';
                     }
                 } else if (event.key === 'ArrowLeft' && index > 0) {
                     this.$refs['digit' + (index - 1)].focus();
                 } else if (event.key === 'ArrowRight' && index < 5) {
                     this.$refs['digit' + (index + 1)].focus();
                 }
             },
             handlePaste(event) {
                 event.preventDefault();
                 const pasteData = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                 if (pasteData.length > 0) {
                     for (let i = 0; i < 6; i++) {
                         this.digits[i] = pasteData[i] || '';
                         if (this.$refs['digit' + i]) {
                             this.$refs['digit' + i].value = this.digits[i];
                         }
                     }
                     const nextFocus = Math.min(pasteData.length, 5);
                     if (this.$refs['digit' + nextFocus]) {
                         this.$refs['digit' + nextFocus].focus();
                     }
                     if (this.fullPin.length === 6) {
                         this.submitForm();
                     }
                 }
             },
             submitForm() {
                 if (this.fullPin.length !== 6 || this.submitting) return;
                 this.submitting = true;
                 this.$refs.form.submit();
             }
         }">

        {{-- Top Shield Icon Badge --}}
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center text-white shadow-xl shadow-indigo-600/30 mb-4 ring-4 ring-indigo-500/10">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>

            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 mb-2.5">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                Autentikasi Dua Langkah
            </span>

            <h1 class="text-2xl font-black text-white tracking-tight">Verifikasi PIN Admin</h1>
            <p class="text-slate-400 text-xs mt-1.5 max-w-xs leading-relaxed">
                Akses panel kontrol memerlukan verifikasi PIN keamanan administrator.
            </p>
        </div>

        {{-- Detected Admin User Info Banner --}}
        @if (Auth::check())
            <div class="mb-6 py-2.5 px-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/70 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600/20 border border-indigo-500/25 flex items-center justify-center flex-shrink-0 text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1 text-left">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Login Terdeteksi:</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-indigo-500/20 text-indigo-300 font-semibold">Admin</span>
                    </div>
                    <div class="text-xs font-bold text-slate-200 truncate mt-0.5">
                        {{ Auth::user()->name }}
                        <span class="text-slate-400 font-normal font-mono text-[11px] ml-1">({{ Auth::user()->email }})</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Flash / Error Messages --}}
        @if ($errors->has('pin'))
            <div class="mb-5 p-3 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold flex items-center gap-2.5 animate-pulse" id="pin-error-alert">
                <svg class="w-4 h-4 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $errors->first('pin') }}</span>
            </div>
        @endif

        {{-- PIN Verification Form --}}
        <form method="POST" action="{{ route('admin.pin.verify') }}" x-ref="form" id="admin-pin-form">
            @csrf

            <input type="hidden" name="pin" :value="fullPin" id="hidden-pin-input">

            {{-- 6-Digit Individual Input Grid --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3 px-1">
                    <label class="text-xs font-bold text-slate-300 tracking-wide uppercase">Masukkan 6 Digit PIN</label>
                    <button type="button" @click="showPin = !showPin"
                            class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 transition focus:outline-none">
                        <svg x-show="!showPin" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPin" x-cloak class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <span x-text="showPin ? 'Sembunyikan' : 'Tampilkan'"></span>
                    </button>
                </div>

                <div class="grid grid-cols-6 gap-2 sm:gap-3" @paste="handlePaste($event)">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="password"
                               x-bind:type="showPin ? 'text' : 'password'"
                               x-ref="digit{{ $i }}"
                               id="pin-digit-{{ $i }}"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               maxlength="1"
                               autocomplete="off"
                               @input="handleInput({{ $i }}, $event)"
                               @keydown="handleKeydown({{ $i }}, $event)"
                               class="w-full aspect-square text-center text-xl sm:text-2xl font-black rounded-2xl bg-slate-800/90 border-2 transition-all duration-200 outline-none select-all text-white placeholder-transparent"
                               :class="digits[{{ $i }}] ? 'border-indigo-500 bg-indigo-950/40 text-indigo-300 ring-2 ring-indigo-500/20' : 'border-slate-700/80 hover:border-slate-600 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20'">
                    @endfor
                </div>
            </div>

            {{-- Submit Button --}}

            <button type="submit"
                    id="verify-pin-btn"
                    :disabled="fullPin.length !== 6 || submitting"
                    class="w-full py-3.5 px-4 rounded-2xl text-sm font-bold tracking-wide transition-all duration-200 flex items-center justify-center gap-2 shadow-lg"
                    :class="fullPin.length === 6 && !submitting
                        ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-indigo-600/30 hover:brightness-110 active:scale-[0.99] cursor-pointer'
                        : 'bg-slate-800 text-slate-500 border border-slate-700/50 cursor-not-allowed opacity-60'">
                <template x-if="submitting">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <template x-if="!submitting">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </template>
                <span x-text="submitting ? 'Memverifikasi...' : 'Verifikasi PIN & Masuk'"></span>
            </button>
        </form>

        {{-- Cancel / Logout Option --}}
        <div class="mt-6 pt-5 border-t border-slate-800/80 flex items-center justify-between text-xs">
            <span class="text-slate-500">Bukan akun Anda?</span>
            <form method="POST" action="{{ route('logout') }}" id="logout-pin-form">
                @csrf
                <button type="submit" id="logout-from-pin-btn"
                        class="text-rose-400 hover:text-rose-300 font-semibold flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar / Ganti Akun
                </button>
            </form>
        </div>

    </div>

    {{-- System Footer --}}
    <div class="relative z-10 mt-6 text-center text-[11px] text-slate-500">
        © {{ date('Y') }} TKA-CBT Platform · Protected with Step-Up Security
    </div>

</body>
</html>
