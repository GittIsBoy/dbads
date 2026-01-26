<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(\App\Services\AdsteraAuthService::class);
    $base = config('adstera.base_url');
    $path = 'placements.json';
    $resolved = rtrim($base, '/') . '/' . ltrim($path, '/');
    echo "Resolved URL: $resolved\n";
    $res = $service->verifyKey();
    echo json_encode(['ok' => true, 'result' => $res], JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
