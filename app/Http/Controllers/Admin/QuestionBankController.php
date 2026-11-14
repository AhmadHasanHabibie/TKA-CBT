<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank;
use App\Models\QuestionBankItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionBankController extends Controller
{
    
    public function index(Request $request)
    {
        $query = QuestionBank::withCount('items')
            ->with(['items' => function ($q) {
                $q->orderBy('order')->orderBy('id');
            }])
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $banks = $query->paginate(12)->withQueryString();

        $categories = QuestionBank::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        return view('admin.question_banks.index', compact('banks', 'categories'));
    }

public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = Auth::id();

        $bank = QuestionBank::create($validated);

        return redirect()->route('admin.question-banks.items', $bank)
            ->with('success', "Album Dokumentasi '{$bank->title}' berhasil dibuat! Silakan unggah foto-foto kegiatan di bawah ini.");
    }

public function update(Request $request, QuestionBank $questionBank)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $questionBank->update($validated);

        return back()->with('success', "Informasi Album '{$questionBank->title}' berhasil diperbarui.");
    }

public function destroy(QuestionBank $questionBank)
    {
        foreach ($questionBank->items as $item) {
            if (!empty($item->image) && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
        }

        $title = $questionBank->title;
        $questionBank->delete();

        return redirect()->route('admin.question-banks.index')
            ->with('success', "Album '{$title}' beserta seluruh fotonya berhasil dihapus.");
    }

public function toggleActive(QuestionBank $questionBank)
    {
        $questionBank->is_active = !$questionBank->is_active;
        $questionBank->save();

        $statusLabel = $questionBank->is_active ? 'dipublikasikan (terlihat oleh siswa)' : 'disimpan sebagai draft';
        return back()->with('success', "Album '{$questionBank->title}' berhasil {$statusLabel}.");
    }

public function items(QuestionBank $questionBank)
    {
        $questionBank->load(['items' => function ($query) {
            $query->orderBy('order')->orderBy('id');
        }]);

        return view('admin.question_banks.items', compact('questionBank'));
    }

public function uploadItems(Request $request, QuestionBank $questionBank)
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'file', 'image', 'max:15360'],
        ], [
            'images.required' => 'Pilih minimal satu file foto dokumentasi untuk diunggah.',
            'images.*.image' => 'File harus berupa gambar (JPG, PNG, WebP, GIF).',
            'images.*.max' => 'Ukuran setiap foto maksimal 15 MB.',
        ]);

        $currentMaxOrder = $questionBank->items()->max('order') ?? 0;
        $uploadedCount = 0;

        foreach ($request->file('images') as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $currentMaxOrder++;
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = "qb_{$questionBank->id}_" . time() . '_' . Str::random(8) . ".{$ext}";
            $path = $file->storeAs("question_banks/{$questionBank->id}", $filename, 'public');

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanTitle = Str::limit(trim($originalName), 100);

            QuestionBankItem::create([
                'question_bank_id' => $questionBank->id,
                'image' => $path,
                'title' => $cleanTitle ?: "Dokumentasi #{$currentMaxOrder}",
                'notes' => null,
                'order' => $currentMaxOrder,
            ]);

            $uploadedCount++;
        }

        return back()->with('success', "Berhasil mengunggah {$uploadedCount} foto ke Album '{$questionBank->title}'!");
    }

public function updateItem(Request $request, QuestionBank $questionBank, QuestionBankItem $item)
    {
        if ($item->question_bank_id !== $questionBank->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $item->update($validated);

        return back()->with('success', 'Informasi foto dokumentasi berhasil diperbarui.');
    }

public function destroyItem(QuestionBank $questionBank, QuestionBankItem $item)
    {
        if ($item->question_bank_id !== $questionBank->id) {
            abort(404);
        }

        if (!empty($item->image) && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return back()->with('success', 'Foto berhasil dihapus dari album dokumentasi.');
    }
}
