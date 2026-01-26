<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

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
        // Ensure SQLite database file exists when using sqlite connection
        if (config('database.default') === 'sqlite' || env('DB_CONNECTION') === 'sqlite') {
            $databasePath = database_path(env('DB_DATABASE', 'database.sqlite'));

            if (!File::exists($databasePath)) {
                // try using the File facade first
                try {
                    File::put($databasePath, '');
                } catch (\Throwable $e) {
                    // ignore and fallback to native functions
                }

                // fallback to native functions if facade failed
                if (!File::exists($databasePath)) {
                    @mkdir(dirname($databasePath), 0755, true);
                    @touch($databasePath);
                }
            }
        }
    }
}
