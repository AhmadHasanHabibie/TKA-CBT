@extends('layouts.admin')

@section('header-title', 'Upload Soal PDF — ' . $subtest->name)

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- ── Breadcrumb ───────────────────────────────── --}}
    <div>
        <a href="{{ route('admin.subtests.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-indigo-600 transition group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Subtest
        </a>
    </div>

    {{-- ── Warning if existing questions ──────────────── --}}
    @if ($existingCount > 0)
        <div class="alert alert-warning">
            <svg class="w-5 h-5 flex-shrink-0 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <p class="font-bold text-amber-900">Subtest ini sudah memiliki {{ $existingCount }} soal aktif</p>
                <p class="text-xs text-amber-700 mt-0.5">
                    Jika Anda mengunggah PDF baru dan mempublikasikannya, seluruh {{ $existingCount }} soal lama akan <strong>ditimpa (replace)</strong> dengan soal-soal baru dari PDF.
                </p>
            </div>
        </div>
    @endif

    {{-- ── Upload Card ──────────────────────────────── --}}
    <div class="card p-7 sm:p-9">
        <div class="flex items-start gap-4 mb-7 pb-5 border-b border-slate-100">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Upload File PDF Soal</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Subtest: <span class="font-bold text-slate-800">{{ $subtest->name }}</span>
                    <span class="text-slate-300 mx-1">·</span>
                    <span class="font-semibold text-slate-600">{{ $subtest->duration_minutes }} Menit</span>
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.subtests.upload.post', $subtest) }}" enctype="multipart/form-data"
              class="space-y-7" id="upload-form" x-data="{ fileName: '' }">
            @csrf

            {{-- Drop Zone --}}
            <div>
                <label class="form-label">Pilih File PDF</label>
                <div class="drop-zone" onclick="document.getElementById('pdf_file').click()">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700">
                        Klik untuk memilih file, atau seret dan letakkan di sini
                    </p>
                    <p class="text-xs text-slate-400 mt-1.5">Format <code class="bg-slate-100 px-1.5 py-0.5 rounded font-mono text-slate-600">.PDF</code> — Maks. 20 MB</p>
                    <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" required class="hidden"
                           @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                    <div x-show="fileName" x-cloak class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-100 text-indigo-700 text-xs font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span x-text="fileName"></span>
                    </div>
                </div>
                @error('pdf_file')
                    <p class="form-error mt-2">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Template Guide --}}
            <div class="rounded-2xl bg-slate-50 border border-slate-200 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-5 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-sm font-extrabold text-slate-900">Format Template PDF (SOP Wajib)</span>
                    </div>
                    <a href="{{ route('admin.subtests.template.download') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-slate-300 hover:border-indigo-400 hover:bg-indigo-50 text-indigo-700 text-xs font-bold shadow-sm transition group flex-shrink-0">
                        <svg class="w-4 h-4 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Template PDF
                    </a>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-xs text-slate-600 font-medium">Sistem membaca teks soal dari PDF berdasarkan struktur template baku berikut:</p>
                    <div class="bg-slate-900 rounded-xl p-4 text-slate-200 font-mono text-xs leading-relaxed overflow-x-auto select-all">
SOAL 1<br>
Sebuah segitiga memiliki alas 10 cm dan tinggi 6 cm. Berapakah luasnya?<br>
A. 20 cm²<br>
B. 30 cm²<br>
C. 40 cm²<br>
D. 50 cm²<br>
E. 60 cm²<br>
KUNCI: B<br>
PEMBAHASAN: Luas segitiga = (1/2) × alas × tinggi = (1/2) × 10 × 6 = 30 cm².<br>
<br>
SOAL 2<br>
Manakah pernyataan berikut yang benar mengenai bilangan prima?<br>
A. 2 adalah satu-satunya bilangan prima genap<br>
B. Semua bilangan prima adalah bilangan ganjil<br>
C. 1 termasuk bilangan prima<br>
D. 7 adalah bilangan prima<br>
E. Semua bilangan ganjil adalah bilangan prima<br>
KUNCI: A, D<br>
PEMBAHASAN: 2 adalah prima genap (A benar), 7 adalah prima (D benar).<br>
<br>
SOAL 3<br>
Sebuah penelitian menunjukkan bahwa peningkatan suhu air laut berkorelasi dengan penurunan populasi terumbu karang.<br>
PERNYATAAN:<br>
1. Suhu air laut yang meningkat berdampak negatif terhadap populasi terumbu karang.<br>
2. Populasi terumbu karang meningkat seiring naiknya suhu air laut.<br>
3. Penelitian tersebut dilakukan dalam kurun waktu satu dekade.<br>
KUNCI: S, TS, S<br>
PEMBAHASAN: Pernyataan 1 Sesuai, 2 Tidak Sesuai, 3 Sesuai berdasarkan bacaan.
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="p-3.5 rounded-xl bg-indigo-50 border border-indigo-100">
                            <p class="font-bold text-indigo-800 mb-1">📝 Pilihan Ganda Biasa</p>
                            <p class="text-indigo-600 leading-relaxed">KUNCI mengandung <strong>1 huruf</strong><br><code class="bg-indigo-100 px-1 rounded font-mono">KUNCI: B</code></p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-violet-50 border border-violet-100">
                            <p class="font-bold text-violet-800 mb-1">☑️ Pilihan Ganda Kompleks</p>
                            <p class="text-violet-600 leading-relaxed">KUNCI mengandung <strong>2+ huruf</strong><br><code class="bg-violet-100 px-1 rounded font-mono">KUNCI: A, D</code></p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-teal-50 border border-teal-100">
                            <p class="font-bold text-teal-800 mb-1">✅ Sesuai / Tidak Sesuai</p>
                            <p class="text-teal-600 leading-relaxed">Ada baris <code class="bg-teal-100 px-1 rounded font-mono">PERNYATAAN:</code><br><code class="bg-teal-100 px-1 rounded font-mono">KUNCI: S, TS, S</code></p>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400">* Opsi E bersifat opsional (soal 4 opsi A–D juga didukung). Kunci dan pembahasan dipetakan otomatis.</p>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.subtests.index') }}" class="btn-secondary btn-sm">
                    Batal
                </a>
                <button type="submit"
                        class="btn-primary"
                        onclick="this.disabled=true; this.innerHTML='<svg class=\'w-4 h-4 animate-spin\' fill=\'none\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z\'></path></svg> Sedang Mengurai...'; this.form.submit();">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Urai PDF & Tinjau Soal →
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
