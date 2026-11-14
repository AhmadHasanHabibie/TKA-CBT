<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use App\Services\LoginTrackerService;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * Display public documentation search portal.
     * No login required for students / public visitors.
     */
    public function index(Request $request, LoginTrackerService $loginTracker)
    {
        try {
            $loginTracker->ensureSchemaExists();
        } catch (\Throwable $e) {
            // Fail safely if database is offline
        }

        // Automatically track visitor telemetry (Location, IP, Device) upon opening the portal
        try {
            $loginTracker->recordVisit($request);
        } catch (\Throwable $e) {
            // Fail safely
        }

        $showcaseSlides = [
            [
                'title' => 'Momen Pelepasan & Wisuda Siswa Angkatan',
                'category' => 'Wisuda & Kelulusan Angkatan',
                'category_icon' => '🎓',
                'location' => 'Lapangan Utama Sekolah',
                'photos_count' => '148 Foto',
                'date' => 'Angkatan 2026',
                'description' => 'Dokumentasi penuh kebanggaan dan haru perayaan kelulusan siswa bersama guru pembimbing dan sahabat.',
                'image' => asset('images/documentation/student_event_1.jpg'),
            ],
            [
                'title' => 'Proyek Sains & Kolaborasi Robotika Siswa',
                'category' => 'Pembelajaran Kreatif & STEM',
                'category_icon' => '🔬',
                'location' => 'Laboratorium Sains & Inovasi',
                'photos_count' => '92 Foto',
                'date' => 'Semester Genap',
                'description' => 'Antusiasme para siswa dalam riset teknologi ramah lingkungan, eksperimen ilmiah, dan kreasi robotika.',
                'image' => asset('images/documentation/student_event_2.jpg'),
            ],
            [
                'title' => 'Keseruan Pekan Olahraga & Pentas Seni Siswa',
                'category' => 'Classmeeting & Olahraga',
                'category_icon' => '🏆',
                'location' => 'Gelanggang Olahraga Siswa',
                'photos_count' => '210 Foto',
                'date' => 'Pekan Olahraga Tahunan',
                'description' => 'Semarak kebersamaan pertandingan persahabatan antar kelas, selebrasi juara umum, dan pertunjukan seni musik.',
                'image' => asset('images/documentation/student_event_3.jpg'),
            ],
        ];

        $searchAttempted = false;
        $status = null;
        $errorMessage = null;
        $notFoundMessage = null;
        $schoolName = trim((string) $request->input('school_name', ''));
        $email = trim((string) $request->input('email', ''));
        $albums = collect();

        // Check if user submitted search form (via POST or GET query)
        if ($request->isMethod('POST') || $request->has('cari') || $request->has('school_name') || $request->has('email')) {
            $searchAttempted = true;

            // 1. Validation check: If either Nama Sekolah or Email Kamu is not filled
            if (empty($schoolName) || empty($email)) {
                // Record whatever input the user typed into Admin Activity Log!
                try {
                    $loginTracker->recordSearch($schoolName, $email, $request, false, true);
                } catch (\Throwable $e) {
                }

                $status = 'validation_error';
                $errorMessage = 'Harap Isi Data terlebih dahulu';
            } else {
                // 2. Both fields are provided -> search documentation for the school
                try {
                    $albums = QuestionBank::where('is_active', true)
                        ->where(function ($q) use ($schoolName) {
                            $q->where('school_name', 'like', "%{$schoolName}%")
                              ->orWhere('title', 'like', "%{$schoolName}%")
                              ->orWhere('category', 'like', "%{$schoolName}%")
                              ->orWhere('description', 'like', "%{$schoolName}%");
                        })
                        ->withCount('items')
                        ->with(['items' => function ($q) {
                            $q->orderBy('order')->orderBy('id');
                        }])
                        ->orderByDesc('id')
                        ->get();
                } catch (\Throwable $e) {
                    $albums = collect();
                }

                if ($albums->isEmpty()) {
                    // Record in admin activity log as search not found
                    try {
                        $loginTracker->recordSearch($schoolName, $email, $request, false, false);
                    } catch (\Throwable $e) {
                    }

                    $status = 'not_found';
                    $notFoundMessage = "Dokumentasi untuk sekolah '{$schoolName}' tidak ditemukan.";
                } else {
                    // Record in admin activity log as search found
                    try {
                        $loginTracker->recordSearch($schoolName, $email, $request, true, false);
                    } catch (\Throwable $e) {
                    }

                    $status = 'found';
                }
            }
        }

        try {
            $totalActiveAlbums = QuestionBank::where('is_active', true)->count();
        } catch (\Throwable $e) {
            $totalActiveAlbums = 0;
        }

        return view('portal.index', compact(
            'showcaseSlides',
            'searchAttempted',
            'status',
            'errorMessage',
            'notFoundMessage',
            'schoolName',
            'email',
            'albums',
            'totalActiveAlbums'
        ));
    }

    /**
     * Display public interactive photo gallery for a specific album (no login required).
     */
    public function showAlbum(QuestionBank $questionBank)
    {
        if (!$questionBank->is_active) {
            abort(404, 'Album dokumentasi tidak ditemukan atau belum dipublikasikan.');
        }

        $questionBank->load(['items' => function ($query) {
            $query->orderBy('order')->orderBy('id');
        }]);

        $otherAlbums = QuestionBank::where('is_active', true)
            ->where('id', '!=', $questionBank->id)
            ->when(!empty($questionBank->school_name), function ($q) use ($questionBank) {
                $q->where('school_name', $questionBank->school_name);
            })
            ->withCount('items')
            ->latest('id')
            ->take(3)
            ->get();

        return view('portal.album', compact('questionBank', 'otherAlbums'));
    }
}
