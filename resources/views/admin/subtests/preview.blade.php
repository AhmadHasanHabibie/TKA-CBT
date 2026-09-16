@extends('layouts.admin')

@section('header-title', 'Tinjau & Edit Soal (Staging Area)')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Summary Bar -->
    <div class="card p-6 sticky top-20 z-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="page-title">Staging Area: {{ $subtest->name }}</h2>
                    <span class="badge badge-indigo">{{ count($questions) }} Soal Terurai</span>
                    @if ($errorCount > 0)
                        <span class="badge badge-rose">{{ $errorCount }} Perlu Dicek</span>
                    @else
                        <span class="badge badge-emerald">✓ Semua Valid</span>
                    @endif
                </div>
                <p class="page-subtitle mt-1">
                    Periksa teks soal, pilihan jawaban, tipe soal, dan kunci jawaban sebelum publikasikan.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('admin.subtests.upload', $subtest) }}" class="btn-secondary btn-sm">
                    Upload Ulang
                </a>
                <button type="submit" form="staging-form" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Publikasikan ke Bank Soal
                </button>
            </div>
        </div>
    </div>

    <!-- Questions Form -->
    <form id="staging-form" method="POST" action="{{ route('admin.subtests.publish', $subtest) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @foreach ($questions as $index => $q)
            @php
                $initialType = $q['type'] ?? (count($q['keys'] ?? []) > 1 ? 'multiple' : 'single');
                $initialKeys = $q['keys'] ?? (!empty($q['key']) ? array_map('trim', explode(',', $q['key'])) : ['A']);
                $initialStatements = ($initialType === 'statement') ? $q['options'] : [
                    ['label' => '1', 'text' => '', 'is_correct' => true],
                    ['label' => '2', 'text' => '', 'is_correct' => false],
                ];
            @endphp

            <div class="bg-white rounded-2xl border transition shadow-sm p-6 sm:p-8 {{ $q['has_error'] ? 'border-rose-300 ring-2 ring-rose-100 bg-rose-50/20' : 'border-slate-200' }}"
                x-data="{
                    type: '{{ $initialType }}',
                    selectedKeys: {{ Js::from($initialKeys) }},
                    statements: {{ Js::from($initialStatements) }},
                    imagePreview: '{{ !empty($q['image']) ? asset('storage/' . $q['image']) : '' }}',
                    hasExistingImage: {{ !empty($q['image']) ? 'true' : 'false' }},
                    deleteImage: false,
                    handleImageUpload(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.deleteImage = false;
                            this.imagePreview = URL.createObjectURL(file);
                            this.hasExistingImage = true;
                        }
                    },
                    removeImage() {
                        this.imagePreview = '';
                        this.hasExistingImage = false;
                        this.deleteImage = true;
                        if (this.$refs.fileInput) {
                            this.$refs.fileInput.value = '';
                        }
                    },
                    toggleKey(label) {
                        if (this.type === 'single') {
                            this.selectedKeys = [label];
                        } else {
                            if (this.selectedKeys.includes(label)) {
                                this.selectedKeys = this.selectedKeys.filter(k => k !== label);
                            } else {
                                this.selectedKeys.push(label);
                            }
                        }
                    },
                    addStatement() {
                        const nextNum = (this.statements.length + 1).toString();
                        this.statements.push({
                            label: nextNum,
                            text: '',
                            is_correct: true
                        });
                    },
                    removeStatement(index) {
                        if (this.statements.length > 2) {
                            this.statements.splice(index, 1);
                            this.statements.forEach((s, idx) => {
                                s.label = (idx + 1).toString();
                            });
                        }
                    }
                }">
                <!-- Hidden inputs for image state -->
                <input type="hidden" name="questions[{{ $index }}][existing_image]" value="{{ $q['image'] ?? '' }}">
                <input type="hidden" name="questions[{{ $index }}][delete_image]" :value="deleteImage ? '1' : '0'">

                <!-- Question Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl font-bold text-xs {{ $q['has_error'] ? 'bg-rose-100 text-rose-800' : 'bg-slate-900 text-white' }}">
                            #{{ $index + 1 }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-900">
                            Soal Nomor {{ $index + 1 }}
                        </h3>

                        <!-- Type Badge -->
                        <span x-show="type === 'single'"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            Pilihan Ganda
                        </span>
                        <span x-show="type === 'multiple'" style="display: none;"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Pilihan Ganda Kompleks (PGK)
                        </span>
                        <span x-show="type === 'statement'" style="display: none;"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                            Sesuai / Tidak Sesuai
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        @if ($q['has_error'])
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Perlu Dicek: {{ $q['error_message'] }}</span>
                            </div>
                        @endif

                        <!-- Type Override Selector -->
                        <div class="flex items-center gap-1.5 bg-slate-50 p-1.5 rounded-xl border border-slate-200">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider pl-1">Tipe:</label>
                            <select name="questions[{{ $index }}][type]" x-model="type"
                                class="text-xs font-bold rounded-lg border border-slate-300 py-1 px-2.5 bg-white text-slate-800 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600">
                                <option value="single">Pilihan Ganda Biasa (1 Jawaban)</option>
                                <option value="multiple">Pilihan Ganda Kompleks (Checkbox)</option>
                                <option value="statement">Sesuai / Tidak Sesuai (Pernyataan)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Question Text / Stimulus -->
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                <span x-show="type === 'statement'">Teks Bacaan / Stimulus</span>
                                <span x-show="type !== 'statement'">Teks Pertanyaan</span>
                            </label>
                            <span class="text-[11px] text-slate-400 font-mono">
                                {{ count(explode("\n", $q['text'])) }} baris • {{ strlen($q['text']) }} karakter
                            </span>
                        </div>
                        <textarea name="questions[{{ $index }}][text]" rows="6" required
                            class="w-full text-sm font-mono rounded-xl border border-slate-300 p-3.5 focus:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600/20 leading-relaxed bg-white text-slate-800 shadow-2xs">{{ $q['text'] }}</textarea>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Mendukung teks panjang, rumus matematika, dan baris matriks. Seluruh teks akan ditampilkan utuh pada lembar ujian.
                        </p>
                    </div>

                    <!-- Image Attachment / Preview Section -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-700">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Gambar / Diagram Soal</span>
                                <span class="text-[11px] font-normal text-slate-400">(Otomatis diekstrak dari PDF atau dapat diunggah mandiri)</span>
                            </label>

                            <template x-if="imagePreview">
                                <button type="button" @click="removeImage()"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Hapus Gambar</span>
                                </button>
                            </template>
                        </div>

                        <!-- Image Preview Box -->
                        <template x-if="imagePreview">
                            <div class="inline-block rounded-xl overflow-hidden border border-slate-200 bg-white p-2 shadow-2xs">
                                <img :src="imagePreview" alt="Pratinjau Gambar Soal #{{ $index + 1 }}"
                                    class="max-h-52 max-w-full rounded-lg object-contain">
                            </div>
                        </template>

                        <!-- File Input -->
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <input type="file" x-ref="fileInput" name="questions[{{ $index }}][image_file]" accept="image/*"
                                @change="handleImageUpload($event)"
                                class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            <span class="text-[11px] text-slate-400" x-text="imagePreview ? 'Pilih file baru jika ingin mengganti gambar soal ini.' : 'Pilih file jika soal ini memerlukan gambar diagram/rumus.'"></span>
                        </div>
                    </div>

                    <!-- Statement Questions Editor -->
                    <template x-if="type === 'statement'">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Daftar Pernyataan & Kunci Jawaban
                                    <span class="font-normal text-slate-500">(Pilih 'Sesuai' atau 'Tidak Sesuai' untuk tiap butir)</span>
                                </label>
                                <button type="button" @click="addStatement()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200 hover:bg-teal-100 transition shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Tambah Pernyataan</span>
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                <template x-for="(stmt, stmtIdx) in statements" :key="stmtIdx">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-50 transition">
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <span class="w-7 h-7 rounded-lg text-xs font-bold flex items-center justify-center bg-teal-600 text-white shadow-2xs"
                                                x-text="stmtIdx + 1"></span>
                                            <input type="hidden" :name="'questions[{{ $index }}][options][' + stmtIdx + '][label]'" :value="(stmtIdx + 1).toString()">
                                        </div>

                                        <input type="text" :name="'questions[{{ $index }}][options][' + stmtIdx + '][text]'"
                                            x-model="stmt.text" required placeholder="Tulis teks pernyataan..."
                                            class="flex-1 text-xs rounded-lg border border-slate-200 px-3 py-2 focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600 bg-white font-medium text-slate-800">

                                        <!-- Segmented Toggle -->
                                        <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200 flex-shrink-0">
                                            <input type="hidden" :name="'questions[{{ $index }}][options][' + stmtIdx + '][is_correct]'" :value="stmt.is_correct ? '1' : '0'">
                                            
                                            <button type="button" @click="stmt.is_correct = true"
                                                class="px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer"
                                                :class="stmt.is_correct ? 'bg-teal-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                                                Sesuai
                                            </button>
                                            
                                            <button type="button" @click="stmt.is_correct = false"
                                                class="px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer"
                                                :class="!stmt.is_correct ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                                                Tidak Sesuai
                                            </button>
                                        </div>

                                        <button type="button" @click="removeStatement(stmtIdx)" :disabled="statements.length <= 2"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 disabled:opacity-30 disabled:cursor-not-allowed transition flex-shrink-0 cursor-pointer"
                                            title="Hapus Pernyataan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Single / Multiple Options Grid -->
                    <template x-if="type !== 'statement'">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Pilihan Jawaban
                                    <span class="font-normal text-slate-500" x-show="type === 'single'">(Pilih 1 radio untuk Kunci Jawaban)</span>
                                    <span class="font-normal text-indigo-600" x-show="type === 'multiple'" style="display: none;">(Centang minimal 2 checkbox untuk Kunci Jawaban PGK)</span>
                                </label>

                                <span x-show="type === 'multiple' && selectedKeys.length < 2" style="display: none;"
                                    class="text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                    ⚠️ PGK wajib memiliki minimal 2 kunci jawaban
                                </span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ($q['options'] as $optIdx => $opt)
                                    @php
                                        $isInitiallyCorrect = $opt['is_correct'] || in_array($opt['label'], $initialKeys);
                                    @endphp
                                    <div class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition"
                                        :class="selectedKeys.includes('{{ $opt['label'] }}') ? 'border-indigo-300 bg-indigo-50/30' : ''">
                                        
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <!-- Single Choice Radio -->
                                            <template x-if="type === 'single'">
                                                <input type="radio" id="q_{{ $index }}_opt_{{ $opt['label'] }}"
                                                    name="questions[{{ $index }}][correct_key]" value="{{ $opt['label'] }}"
                                                    :checked="selectedKeys.includes('{{ $opt['label'] }}')"
                                                    @change="selectedKeys = ['{{ $opt['label'] }}']"
                                                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                                            </template>

                                            <!-- Multiple Choice Checkbox -->
                                            <template x-if="type === 'multiple'">
                                                <input type="checkbox" id="q_{{ $index }}_opt_cb_{{ $opt['label'] }}"
                                                    name="questions[{{ $index }}][correct_keys][]" value="{{ $opt['label'] }}"
                                                    :checked="selectedKeys.includes('{{ $opt['label'] }}')"
                                                    @change="toggleKey('{{ $opt['label'] }}')"
                                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                                            </template>

                                            <label :for="type === 'single' ? 'q_{{ $index }}_opt_{{ $opt['label'] }}' : 'q_{{ $index }}_opt_cb_{{ $opt['label'] }}'"
                                                class="w-7 h-7 rounded-lg text-xs font-bold flex items-center justify-center cursor-pointer transition"
                                                :class="selectedKeys.includes('{{ $opt['label'] }}') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-slate-300 text-slate-700'">
                                                {{ $opt['label'] }}
                                            </label>
                                            <input type="hidden" name="questions[{{ $index }}][options][{{ $optIdx }}][label]" value="{{ $opt['label'] }}">
                                        </div>

                                        <input type="text" name="questions[{{ $index }}][options][{{ $optIdx }}][text]"
                                            value="{{ $opt['text'] }}" required
                                            class="flex-1 text-xs rounded-lg border border-slate-200 px-3 py-1.5 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600 bg-white">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </template>

                    <!-- Explanation (Pembahasan) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pembahasan Soal (PEMBAHASAN)
                        </label>
                        <textarea name="questions[{{ $index }}][explanation]" rows="2" placeholder="Tulis pembahasan atau rumus jawaban di sini..."
                            class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600/20 bg-slate-50/30">{{ $q['explanation'] }}</textarea>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Bottom Publish Button -->
        <div class="flex items-center justify-between p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">
                Pastikan seluruh {{ count($questions) }} soal sudah sesuai dengan kunci jawaban sebelum mempublikasikan.
            </p>
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Publikasikan Seluruh Soal Sekarang</span>
            </button>
        </div>
    </form>
</div>
@endsection
