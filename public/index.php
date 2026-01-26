<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Helper: try several possible project roots so this file still works
// when public/ is moved into public_html and project files live in a sibling folder.
$possibleRoots = [
    realpath(__DIR__.'/..'),            // ../ (default when public is inside project)
    realpath(__DIR__.'/../laravel-app'),
    realpath(__DIR__.'/../app'),
    realpath(__DIR__.'/../../laravel-app'),
    realpath(__DIR__.'/../../'),
];

$projectRoot = null;
$autoload = null;
$bootstrapApp = null;
foreach ($possibleRoots as $r) {
    if (!$r) continue;
    $candidateAutoload = $r . '/vendor/autoload.php';
    $candidateApp = $r . '/bootstrap/app.php';
    if (file_exists($candidateAutoload) && file_exists($candidateApp)) {
        $projectRoot = $r;
        $autoload = $candidateAutoload;
        $bootstrapApp = $candidateApp;
        break;
    }
}

// Fallback to the original relative paths if nothing found above
if (!$autoload) {
    $autoload = __DIR__ . '/../vendor/autoload.php';
    $bootstrapApp = __DIR__ . '/../bootstrap/app.php';
    $projectRoot = realpath(__DIR__ . '/..');
}

// Determine if the application is in maintenance mode (search using detected project root)
$maintenancePath = $projectRoot . '/storage/framework/maintenance.php';
if (file_exists($maintenancePath)) {
    require $maintenancePath;
}

// Register the Composer autoloader
require $autoload;

// Bootstrap Laravel and handle the request
/** @var Application $app */
$app = require_once $bootstrapApp;

// Handle the request (use Request facade capture for compatibility)
$app->handleRequest(Request::capture());
