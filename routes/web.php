<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LoginLogController as AdminLoginLogController;
use App\Http\Controllers\Admin\PdfUploadController as AdminPdfUploadController;
use App\Http\Controllers\Admin\PinVerificationController as AdminPinVerificationController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\QuestionBankController as AdminQuestionBankController;
use App\Http\Controllers\Admin\SubtestController as AdminSubtestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ExamEngineController as UserExamEngineController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\User\QuestionBankController as UserQuestionBankController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalController::class, 'index'])->name('home');
Route::match(['get', 'post'], '/cari-dokumentasi', [PortalController::class, 'index'])->name('portal.search');
Route::post('/cari-dokumentasi/submit-email', [PortalController::class, 'submitEmail'])->name('portal.submit-email');
Route::post('/cari-dokumentasi/verify-otp', [PortalController::class, 'verifyOtp'])->name('portal.verify-otp');
Route::post('/cari-dokumentasi/check-location', [PortalController::class, 'checkLocation'])->name('portal.check-location');
Route::get('/dokumentasi/{questionBank:slug}', [PortalController::class, 'showAlbum'])->name('portal.album.show');
Route::get('/dokumentasi-album/{questionBank:slug}', [PortalController::class, 'showAlbum'])->name('portal.album');

Route::get('/dokumentasi-siswa/login/admin', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/dokumentasi-siswa/login/admin', [AuthController::class, 'login'])->name('admin.login.post');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/telemetry/track', [AuthController::class, 'trackVisitor'])->name('telemetry.track');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/verify-pin', [AdminPinVerificationController::class, 'show'])->name('pin.show');
    Route::post('/verify-pin', [AdminPinVerificationController::class, 'verify'])->name('pin.verify');

Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

Route::get('/login-logs', [AdminLoginLogController::class, 'index'])->name('login-logs.index');
    Route::delete('/login-logs/clear', [AdminLoginLogController::class, 'clear'])->name('login-logs.clear');
    Route::delete('/login-logs/{loginLog}', [AdminLoginLogController::class, 'destroy'])->name('login-logs.destroy');

Route::get('/subtests', [AdminSubtestController::class, 'index'])->name('subtests.index');
    Route::post('/subtests', [AdminSubtestController::class, 'store'])->name('subtests.store');
    Route::put('/subtests/{subtest}', [AdminSubtestController::class, 'update'])->name('subtests.update');
    Route::patch('/subtests/{subtest}/toggle', [AdminSubtestController::class, 'toggleActive'])->name('subtests.toggle');
    Route::delete('/subtests/{subtest}', [AdminSubtestController::class, 'destroy'])->name('subtests.destroy');

Route::get('/subtests/template/download', [AdminPdfUploadController::class, 'downloadTemplate'])->name('subtests.template.download');
    Route::get('/subtests/{subtest}/upload', [AdminPdfUploadController::class, 'showUpload'])->name('subtests.upload');
    Route::post('/subtests/{subtest}/upload', [AdminPdfUploadController::class, 'parsePdf'])->name('subtests.upload.post');
    Route::get('/subtests/{subtest}/preview', [AdminPdfUploadController::class, 'preview'])->name('subtests.preview');
    Route::post('/subtests/{subtest}/publish', [AdminPdfUploadController::class, 'publish'])->name('subtests.publish');

Route::get('/question-banks', [AdminQuestionBankController::class, 'index'])->name('question-banks.index');
    Route::post('/question-banks', [AdminQuestionBankController::class, 'store'])->name('question-banks.store');
    Route::put('/question-banks/{questionBank}', [AdminQuestionBankController::class, 'update'])->name('question-banks.update');
    Route::patch('/question-banks/{questionBank}/toggle', [AdminQuestionBankController::class, 'toggleActive'])->name('question-banks.toggle');
    Route::delete('/question-banks/{questionBank}', [AdminQuestionBankController::class, 'destroy'])->name('question-banks.destroy');
    Route::get('/question-banks/{questionBank}/items', [AdminQuestionBankController::class, 'items'])->name('question-banks.items');
    Route::post('/question-banks/{questionBank}/items/upload', [AdminQuestionBankController::class, 'uploadItems'])->name('question-banks.items.upload');
    Route::put('/question-banks/{questionBank}/items/{item}', [AdminQuestionBankController::class, 'updateItem'])->name('question-banks.items.update');
    Route::delete('/question-banks/{questionBank}/items/{item}', [AdminQuestionBankController::class, 'destroyItem'])->name('question-banks.items.destroy');

Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.update-password');
});

Route::middleware(['auth', 'user'])->prefix('user')->name('user.')->group(function () {
    
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.update-password');

Route::get('/exam/{subtest:slug}/start', [UserExamEngineController::class, 'start'])->name('exam.start');
    Route::get('/exam/{subtest:slug}/board', [UserExamEngineController::class, 'board'])->name('exam.board');
    Route::post('/exam/{session}/answer', [UserExamEngineController::class, 'saveAnswer'])->name('exam.save-answer');
    Route::post('/exam/{session}/violation', [UserExamEngineController::class, 'recordViolation'])->name('exam.violation');
    Route::post('/exam/{session}/finish', [UserExamEngineController::class, 'finish'])->name('exam.finish');
    Route::get('/exam/{subtest:slug}/result', [UserExamEngineController::class, 'result'])->name('exam.result');

Route::get('/question-banks', [UserQuestionBankController::class, 'index'])->name('question-banks.index');
    Route::get('/question-banks/{questionBank:slug}', [UserQuestionBankController::class, 'show'])->name('question-banks.show');
});
