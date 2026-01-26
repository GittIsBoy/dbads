<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

use Exception;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure SQLite database file exists when using sqlite connection on platforms
        // where we may not have console access (e.g., Railway). This creates the
        // database file path if it does not exist to avoid runtime failures when
        // session/cache drivers use the database.
        try {
            $connection = config('database.default');
            if ($connection === 'sqlite') {
                $databasePath = config('database.connections.sqlite.database');
                // ignore if in-memory or empty
                if (!empty($databasePath) && $databasePath !== ':memory:') {
                    // ensure absolute path
                    if ($databasePath[0] !== DIRECTORY_SEPARATOR) {
                        $databasePath = base_path($databasePath);
                    }
                    $dir = dirname($databasePath);
                    if (!File::exists($dir)) {
                        File::makeDirectory($dir, 0755, true);
                    }
                    if (!File::exists($databasePath)) {
                        // create empty sqlite file
                        File::put($databasePath, '');
                        // fallback to native functions if facade failed for any reason
                        if (!File::exists($databasePath)) {
                            @mkdir(dirname($databasePath), 0755, true);
                            @touch($databasePath);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // don't break the application boot if this fails; logging will capture it
            if (function_exists('logger')) {
                logger()->warning('Failed to ensure sqlite database file: ' . $e->getMessage());
            }
        }
    }
}
