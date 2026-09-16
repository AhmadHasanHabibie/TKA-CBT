<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subtest;
use Illuminate\Http\Request;

class SubtestController extends Controller
{
    /**
     * Display a listing of subtests.
     */
    public function index()
    {
        $subtests = Subtest::withCount(['questions', 'examSessions'])
            ->latest()
            ->paginate(15);

        return view('admin.subtests.index', compact('subtests'));
    }

    /**
     * Store a newly created subtest and redirect to PDF upload.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:360'],
        ], [
            'name.required' => 'Nama subtest wajib diisi.',
            'duration_minutes.required' => 'Durasi pengerjaan wajib diisi.',
            'duration_minutes.min' => 'Durasi minimal 1 menit.',
        ]);

        $subtest = Subtest::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'total_questions' => 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.subtests.upload', $subtest)
            ->with('success', "Subtest '{$subtest->name}' berhasil dibuat. Silakan upload file PDF soal.");
    }

    /**
     * Update the specified subtest in storage.
     */
    public function update(Request $request, Subtest $subtest)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:360'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $subtest->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'is_active' => $request->boolean('is_active', $subtest->is_active),
        ]);

        return redirect()->route('admin.subtests.index')
            ->with('success', "Subtest '{$subtest->name}' berhasil diperbarui.");
    }

    /**
     * Toggle the active status of a subtest.
     */
    public function toggleActive(Subtest $subtest)
    {
        $subtest->update([
            'is_active' => !$subtest->is_active,
        ]);

        $statusText = $subtest->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.subtests.index')
            ->with('success', "Subtest '{$subtest->name}' berhasil {$statusText}.");
    }

    /**
     * Remove the specified subtest from storage.
     */
    public function destroy(Subtest $subtest)
    {
        $name = $subtest->name;
        $subtest->delete();

        return redirect()->route('admin.subtests.index')
            ->with('success', "Subtest '{$name}' beserta seluruh soal dan riwayatnya berhasil dihapus.");
    }
}
