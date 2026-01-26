<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(\App\Services\AdsteraAuthService::class);
    $finish = date('Y-m-d');
    $start = date('Y-m-d', strtotime('-6 days'));
    $params = [
        'start_date' => $start,
        'finish_date' => $finish,
        'group_by[]' => 'date',
    ];

    $res = $service->request('GET', 'stats.json', ['query' => $params]);
    echo json_encode(['ok' => true, 'response' => $res], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (\Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
