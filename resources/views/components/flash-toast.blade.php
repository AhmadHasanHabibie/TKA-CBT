{{--
    Custom Flash Toast Component (Full Custom Toast Notifications)
    Handles server-side session alerts AND client-side window.showToast('message', 'type')
--}}
<div x-data="{
    toasts: [],
    add(message, type = 'success', duration = 5000) {
        const id = Date.now() + Math.random();
        this.toasts.push({ id, message, type, visible: true });
        if (duration > 0) {
            setTimeout(() => this.remove(id), duration);
        }
    },
    remove(id) {
        const index = this.toasts.findIndex(t => t.id === id);
        if (index > -1) {
            this.toasts[index].visible = false;
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 300);
        }
    },
    init() {
        @if (session('success'))
            this.add({{ Js::from(session('success')) }}, 'success');
        @endif
        @if (session('error'))
            this.add({{ Js::from(session('error')) }}, 'danger');
        @endif
        @if (session('warning'))
            this.add({{ Js::from(session('warning')) }}, 'warning');
        @endif
        @if (session('info') || session('status'))
            this.add({{ Js::from(session('info') ?? session('status')) }}, 'info');
        @endif
    }
}"
@toast.window="add($event.detail.message, $event.detail.type || 'info', $event.detail.duration || 5000)"
class="fixed top-5 right-5 z-[110] flex flex-col gap-2.5 max-w-sm w-full pointer-events-none px-4 sm:px-0">

    <template x-for="t in toasts" :key="t.id">
        <div x-show="t.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-8 scale-95"
             class="pointer-events-auto flex items-start gap-3 p-4 rounded-2xl bg-white border shadow-xl shadow-slate-900/10 text-slate-800"
             :class="t.type === 'danger' || t.type === 'error'
                ? 'border-rose-200 ring-1 ring-rose-500/10'
                : t.type === 'warning'
                ? 'border-amber-200 ring-1 ring-amber-500/10'
                : t.type === 'info'
                ? 'border-indigo-200 ring-1 ring-indigo-500/10'
                : 'border-emerald-200 ring-1 ring-emerald-500/10'">

            {{-- Icon Container --}}
            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                 :class="t.type === 'danger' || t.type === 'error'
                    ? 'bg-rose-50 text-rose-600'
                    : t.type === 'warning'
                    ? 'bg-amber-50 text-amber-600'
                    : t.type === 'info'
                    ? 'bg-indigo-50 text-indigo-600'
                    : 'bg-emerald-50 text-emerald-600'">

                {{-- Danger --}}
                <template x-if="t.type === 'danger' || t.type === 'error'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </template>

                {{-- Warning --}}
                <template x-if="t.type === 'warning'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </template>

                {{-- Info --}}
                <template x-if="t.type === 'info'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </template>

                {{-- Success --}}
                <template x-if="t.type !== 'danger' && t.type !== 'error' && t.type !== 'warning' && t.type !== 'info'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </template>
            </div>

            {{-- Message Body --}}
            <div class="flex-1 min-w-0 pt-0.5">
                <p class="text-xs font-bold text-slate-900"
                   x-text="t.type === 'danger' || t.type === 'error' ? 'Pemberitahuan Kesalahan' : t.type === 'warning' ? 'Peringatan' : t.type === 'info' ? 'Informasi' : 'Berhasil'"></p>
                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed break-words" x-text="t.message"></p>
            </div>

            {{-- Close Button --}}
            <button type="button" @click="remove(t.id)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </template>
</div>
