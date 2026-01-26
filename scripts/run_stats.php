<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $controller = app(\App\Http\Controllers\DashboardController::class);
    $request = new Illuminate\Http\Request();
    $response = $controller->stats($request);
    if (method_exists($response, 'getContent')) {
        echo $response->getContent();
    } else {
        echo json_encode($response);
    }
} catch (\Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
