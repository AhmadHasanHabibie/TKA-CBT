<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PdfUploadController as AdminPdfUploadController;
use App\Http\Controllers\Admin\QuestionBankController as AdminQuestionBankController;
use App\Http\Controllers\Admin\SubtestController as AdminSubtestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ExamEngineController as UserExamEngineController;
use App\Http\Controllers\User\QuestionBankController as UserQuestionBankController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — TKA Computer-Based Test (CBT)
|--------------------------------------------------------------------------
*/

// Root URL redirects to login or dashboard depending on session
Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');

// Authentication Routes (Strictly manual, no public registration)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// Admin Route Group (Protected by 'admin' middleware)
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Subtest Management
    Route::get('/subtests', [AdminSubtestController::class, 'index'])->name('subtests.index');
    Route::post('/subtests', [AdminSubtestController::class, 'store'])->name('subtests.store');
    Route::put('/subtests/{subtest}', [AdminSubtestController::class, 'update'])->name('subtests.update');
    Route::patch('/subtests/{subtest}/toggle', [AdminSubtestController::class, 'toggleActive'])->name('subtests.toggle');
    Route::delete('/subtests/{subtest}', [AdminSubtestController::class, 'destroy'])->name('subtests.destroy');

    // PDF Upload & Staging & Template Download
    Route::get('/subtests/template/download', [AdminPdfUploadController::class, 'downloadTemplate'])->name('subtests.template.download');
    Route::get('/subtests/{subtest}/upload', [AdminPdfUploadController::class, 'showUpload'])->name('subtests.upload');
    Route::post('/subtests/{subtest}/upload', [AdminPdfUploadController::class, 'parsePdf'])->name('subtests.upload.post');
    Route::get('/subtests/{subtest}/preview', [AdminPdfUploadController::class, 'preview'])->name('subtests.preview');
    Route::post('/subtests/{subtest}/publish', [AdminPdfUploadController::class, 'publish'])->name('subtests.publish');

    // Question Banks Management (Bank Soal Multi-Foto)
    Route::get('/question-banks', [AdminQuestionBankController::class, 'index'])->name('question-banks.index');
    Route::post('/question-banks', [AdminQuestionBankController::class, 'store'])->name('question-banks.store');
    Route::put('/question-banks/{questionBank}', [AdminQuestionBankController::class, 'update'])->name('question-banks.update');
    Route::patch('/question-banks/{questionBank}/toggle', [AdminQuestionBankController::class, 'toggleActive'])->name('question-banks.toggle');
    Route::delete('/question-banks/{questionBank}', [AdminQuestionBankController::class, 'destroy'])->name('question-banks.destroy');
    Route::get('/question-banks/{questionBank}/items', [AdminQuestionBankController::class, 'items'])->name('question-banks.items');
    Route::post('/question-banks/{questionBank}/items/upload', [AdminQuestionBankController::class, 'uploadItems'])->name('question-banks.items.upload');
    Route::put('/question-banks/{questionBank}/items/{item}', [AdminQuestionBankController::class, 'updateItem'])->name('question-banks.items.update');
    Route::delete('/question-banks/{questionBank}/items/{item}', [AdminQuestionBankController::class, 'destroyItem'])->name('question-banks.items.destroy');
});

// ==========================================
// User / Student Route Group (Protected by 'user' middleware)
// ==========================================
Route::middleware(['auth', 'user'])->prefix('user')->name('user.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // CBT Exam Engine
    Route::get('/exam/{subtest:slug}/start', [UserExamEngineController::class, 'start'])->name('exam.start');
    Route::get('/exam/{subtest:slug}/board', [UserExamEngineController::class, 'board'])->name('exam.board');
    Route::post('/exam/{session}/answer', [UserExamEngineController::class, 'saveAnswer'])->name('exam.save-answer');
    Route::post('/exam/{session}/violation', [UserExamEngineController::class, 'recordViolation'])->name('exam.violation');
    Route::post('/exam/{session}/finish', [UserExamEngineController::class, 'finish'])->name('exam.finish');
    Route::get('/exam/{subtest:slug}/result', [UserExamEngineController::class, 'result'])->name('exam.result');

    // Question Banks (Bank Soal untuk Siswa)
    Route::get('/question-banks', [UserQuestionBankController::class, 'index'])->name('question-banks.index');
    Route::get('/question-banks/{questionBank:slug}', [UserQuestionBankController::class, 'show'])->name('question-banks.show');
});
