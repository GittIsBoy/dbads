
<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

$controller = app(\App\Http\Controllers\DashboardController::class);
$request = Request::create('/api/dashboard/stats', 'GET', []);
$response = $controller->stats($request);

if (is_object($response) && method_exists($response, 'getContent')) {
    echo $response->getContent();
} elseif (is_string($response)) {
    echo $response;
} else {
    echo json_encode(['error' => 'unexpected response type', 'type' => gettype($response)]);
}
