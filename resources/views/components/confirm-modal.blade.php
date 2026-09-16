{{--
    Custom Confirm Modal Component (Full Custom, Global, Accessible)
    Usage anywhere:
    window.showConfirm({
        title: 'Hapus Subtest',
        message: 'Apakah Anda yakin?',
        type: 'danger', // danger | warning | info | success
        confirmText: 'Ya, Hapus Sekarang',
        cancelText: 'Batal',
        action: () => document.getElementById('my-form').submit()
    });
--}}
<div x-data="{
    open: false,
    type: 'danger',
    title: 'Konfirmasi Tindakan',
    message: '',
    confirmText: 'Ya, Lanjutkan',
    cancelText: 'Batal',
    onConfirm: null,
    show(detail) {
        if (!detail) return;
        this.type = detail.type || 'danger';
        this.title = detail.title || 'Konfirmasi Tindakan';
        this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        this.confirmText = detail.confirmText || 'Ya, Lanjutkan';
        this.cancelText = detail.cancelText || 'Batal';
        this.onConfirm = typeof detail.action === 'function' ? detail.action : null;
        this.open = true;
    },
    confirm() {
        this.open = false;
        if (typeof this.onConfirm === 'function') {
            this.onConfirm();
        }
    }
}"
@confirm.window="show($event.detail)"
x-cloak
x-show="open"
x-transition:enter="transition ease-out duration-200"
x-transition:enter-start="opacity-0"
x-transition:enter-end="opacity-100"
x-transition:leave="transition ease-in duration-150"
x-transition:leave-start="opacity-100"
x-transition:leave-end="opacity-0"
class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md"
@keydown.escape.window="open = false">

    {{-- Modal Box with scale transition --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         @click.away="open = false"
         class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">

        {{-- Top Accent Bar --}}
        <div class="h-1.5 w-full"
             :class="type === 'danger'
                ? 'bg-gradient-to-r from-rose-500 to-rose-600'
                : type === 'warning'
                ? 'bg-gradient-to-r from-amber-400 to-amber-500'
                : type === 'success'
                ? 'bg-gradient-to-r from-emerald-400 to-teal-500'
                : 'bg-gradient-to-r from-indigo-500 to-indigo-600'">
        </div>

        {{-- Content Area --}}
        <div class="p-6 sm:p-7">
            <div class="flex items-start gap-4">
                {{-- Icon Badge --}}
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm"
                     :class="type === 'danger'
                        ? 'bg-rose-50 text-rose-600 border border-rose-100'
                        : type === 'warning'
                        ? 'bg-amber-50 text-amber-600 border border-amber-100'
                        : type === 'success'
                        ? 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                        : 'bg-indigo-50 text-indigo-600 border border-indigo-100'">

                    {{-- Danger Icon --}}
                    <template x-if="type === 'danger'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </template>

                    {{-- Warning Icon --}}
                    <template x-if="type === 'warning'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </template>

                    {{-- Success Icon --}}
                    <template x-if="type === 'success'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>

                    {{-- Info Icon --}}
                    <template x-if="type !== 'danger' && type !== 'warning' && type !== 'success'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                </div>

                <div class="flex-1 min-w-0 pt-0.5">
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight" x-text="title"></h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed" x-text="message"></p>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
            <button type="button"
                    @click="open = false"
                    class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition">
                <span x-text="cancelText">Batal</span>
            </button>
            <button type="button"
                    @click="confirm()"
                    class="px-5 py-2 rounded-xl text-white text-xs font-bold shadow-md transition transform active:scale-95"
                    :class="type === 'danger'
                        ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/25'
                        : type === 'warning'
                        ? 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/25'
                        : type === 'success'
                        ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/25'
                        : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/25'"
                    x-text="confirmText">
            </button>
        </div>
    </div>
</div>
