<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sessionFiles = glob(__DIR__ . '/storage/framework/sessions/*');
foreach ($sessionFiles as $file) {
    if (basename($file) === '.gitignore') continue;
    $raw = file_get_contents($file);
    $data = @unserialize($raw);
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            if (str_starts_with($k, 'subtest_staging_')) {
                echo "Found $k in $file\n";
                echo "Question 1:\n";
                print_r($v[0]);
                break;
            }
        }
    }
}
