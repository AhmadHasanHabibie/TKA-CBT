<?php
/**
 * Diagnostik & Self-Healing Hosting cPanel — Portal Dokumentasi Foto Siswa
 * Akses file ini via browser: https://cbt.tokobiie.my.id/diagnose.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$basePath = dirname(__DIR__);

// Fungsi helper auto-create folder dengan permission 0775
function ensureDir($path) {
    if (!file_exists($path)) {
        @mkdir($path, 0775, true);
    }
    return is_dir($path) && is_writable($path);
}

// 1. Periksa & auto-perbaiki direktori storage & cache
$dirsToFix = [
    $basePath . '/storage',
    $basePath . '/storage/app',
    $basePath . '/storage/app/public',
    $basePath . '/storage/framework',
    $basePath . '/storage/framework/cache',
    $basePath . '/storage/framework/cache/data',
    $basePath . '/storage/framework/sessions',
    $basePath . '/storage/framework/views',
    $basePath . '/storage/logs',
    $basePath . '/bootstrap/cache',
];

$dirResults = [];
foreach ($dirsToFix as $dir) {
    $writable = ensureDir($dir);
    $rel = str_replace($basePath . '/', '', $dir);
    $dirResults[$rel] = $writable;
}

// 2. Baca .env
$envPath = $basePath . '/.env';
$envExists = file_exists($envPath);
$envData = [];
if ($envExists) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $envData[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
    }
}

// 3. Tes Koneksi Database
$dbStatus = 'Belum Dikonfigurasi';
$dbMessage = '';
$dbIsOk = false;
$dbConnection = $envData['DB_CONNECTION'] ?? 'mysql';

if ($dbConnection === 'sqlite') {
    $sqliteFile = $basePath . '/database/database.sqlite';
    if (!file_exists($sqliteFile)) {
        @touch($sqliteFile);
    }
    $dbIsOk = file_exists($sqliteFile) && is_writable($sqliteFile);
    $dbMessage = $dbIsOk ? 'Database SQLite Aktif & Dapat Ditulis' : 'File database.sqlite tidak dapat dibuat/ditulis';
} else {
    $host = $envData['DB_HOST'] ?? '127.0.0.1';
    $port = $envData['DB_PORT'] ?? '3306';
    $dbname = $envData['DB_DATABASE'] ?? '';
    $user = $envData['DB_USERNAME'] ?? '';
    $pass = $envData['DB_PASSWORD'] ?? '';

    if (empty($dbname) || empty($user)) {
        $dbStatus = 'Kredensial Belum Diisi';
        $dbMessage = 'DB_DATABASE atau DB_USERNAME di .env masih kosong atau belum disesuaikan dengan database cPanel.';
    } else {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $dbIsOk = true;
            $dbStatus = 'Terhubung';
            $dbMessage = "Berhasil terhubung ke database `{$dbname}` di {$host}!";

            // Auto-normalisasi nama sekolah di tabel question_banks agar tidak ada duplikat huruf besar/kecil
            try {
                $pdo->exec("UPDATE question_banks SET school_name = 'SMAN 25 Jakarta' WHERE LOWER(TRIM(school_name)) = 'sman 25 jakarta'");
            } catch (\Throwable $e) {}
        } catch (\Throwable $e) {
            $dbIsOk = false;
            $dbStatus = 'Gagal';
            $dbMessage = $e->getMessage();
        }
    }
}

// 4. Periksa PHP Version & Ekstensi
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '8.1.0', '>=');

$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'fileinfo', 'curl', 'ctype', 'json'];
$extensionResults = [];
foreach ($requiredExtensions as $ext) {
    $extensionResults[$ext] = extension_loaded($ext);
}

// 5. Periksa APP_KEY
$appKey = $envData['APP_KEY'] ?? '';
$hasAppKey = !empty($appKey);

// 6. Periksa Vite Build Manifest
$manifestPath = __DIR__ . '/build/manifest.json';
$hasManifest = file_exists($manifestPath);

// 7. Periksa apakah cache Windows lama ada di bootstrap/cache
$windowsCacheDetected = false;
$configCache = $basePath . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    $content = file_get_contents($configCache);
    if (str_contains($content, 'C:\\') || str_contains($content, 'c:\\')) {
        $windowsCacheDetected = true;
        @unlink($configCache); // Auto hapus cache Windows yang merusak Linux
    }
}
$routesCache = $basePath . '/bootstrap/cache/routes-v7.php';
if (file_exists($routesCache)) {
    @unlink($routesCache); // Bersihkan route cache
}
$viewFiles = glob($basePath . '/storage/framework/views/*.php');
if ($viewFiles) {
    foreach ($viewFiles as $vf) {
        @unlink($vf); // Bersihkan view cache Blade
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeriksaan Status Server & Hosting — Diagnostik</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-4 sm:p-8">
<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center gap-3 pb-6 border-b border-slate-800">
            <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center font-black text-white text-lg">⚡</div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Diagnostik Server & Hosting</h1>
                <p class="text-xs sm:text-sm text-slate-400">Pengecekan otomatis konfigurasi server cPanel untuk mengatasi error 500</p>
            </div>
        </div>

        <div class="mt-6 space-y-4">

            <!-- 1. PHP Version -->
            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-800/60 border border-slate-700/50">
                <div>
                    <div class="font-bold text-sm text-white">Versi PHP</div>
                    <div class="text-xs text-slate-400">Terdeteksi: PHP <?= $phpVersion ?> (Dibutuhkan: >= 8.1)</div>
                </div>
                <span class="px-3 py-1 rounded-xl text-xs font-bold <?= $phpOk ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' ?>">
                    <?= $phpOk ? '✓ Memenuhi Syarat' : '✗ Terlalu Rendah' ?>
                </span>
            </div>

            <!-- 2. File .env & APP_KEY -->
            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-800/60 border border-slate-700/50">
                <div>
                    <div class="font-bold text-sm text-white">Konfigurasi .env & APP_KEY</div>
                    <div class="text-xs text-slate-400">
                        <?= $envExists ? 'File .env ditemukan' : 'File .env TIDAK ditemukan!' ?> · 
                        <?= $hasAppKey ? 'APP_KEY terisi' : '<span class="text-rose-400 font-bold">APP_KEY KOSONG!</span>' ?>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-xl text-xs font-bold <?= ($envExists && $hasAppKey) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' ?>">
                    <?= ($envExists && $hasAppKey) ? '✓ Sesuai' : '✗ Perlu Diisi' ?>
                </span>
            </div>

            <!-- 3. Koneksi Database -->
            <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/50 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm text-white">Koneksi Database MySQL</div>
                        <div class="text-xs text-slate-400">Driver: <?= htmlspecialchars($dbConnection) ?></div>
                    </div>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold <?= $dbIsOk ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' ?>">
                        <?= $dbIsOk ? '✓ Berhasil Terhubung' : '✗ ' . $dbStatus ?>
                    </span>
                </div>
                <div class="p-3 rounded-xl bg-slate-900 text-xs font-mono <?= $dbIsOk ? 'text-emerald-300' : 'text-rose-300' ?> break-words">
                    <?= htmlspecialchars($dbMessage) ?>
                </div>
            </div>

            <!-- 4. Izin Tulis Folder Storage & Cache -->
            <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/50 space-y-2">
                <div class="font-bold text-sm text-white mb-2">Izin Tulis Folder (Storage & Cache)</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <?php foreach ($dirResults as $dir => $writable): ?>
                        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-800">
                            <span class="font-mono text-slate-300"><?= $dir ?></span>
                            <span class="font-bold <?= $writable ? 'text-emerald-400' : 'text-rose-400' ?>">
                                <?= $writable ? '✓ Writable' : '✗ Readonly' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 5. Vite Build Manifest -->
            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-800/60 border border-slate-700/50">
                <div>
                    <div class="font-bold text-sm text-white">Aset Tampilan (Vite Build)</div>
                    <div class="text-xs text-slate-400">
                        <?= $hasManifest ? 'File public/build/manifest.json ditemukan' : 'Belum di-build (Sistem otomatis fallback ke CDN)' ?>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-xl text-xs font-bold <?= $hasManifest ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' ?>">
                    <?= $hasManifest ? '✓ Aset Siap' : '⚡ Aman (Fallback CDN)' ?>
                </span>
            </div>

            <!-- 6. Windows Cache Auto-clean -->
            <?php if ($windowsCacheDetected): ?>
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-3">
                    <span class="text-lg">🧹</span>
                    <span>Cache Windows lokal lama terdeteksi dan <strong>sudah otomatis dibersihkan</strong> oleh script ini agar server Linux cPanel tidak error!</span>
                </div>
            <?php endif; ?>

        </div>

        <div class="mt-8 pt-6 border-t border-slate-800 flex flex-wrap gap-3 justify-end">
            <a href="/" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition shadow-lg shadow-indigo-600/30">
                Buka Website Sekarang →
            </a>
            <button onclick="window.location.reload()" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition border border-slate-700">
                Periksa Ulang
            </button>
        </div>
    </div>

</div>
</body>
</html>

