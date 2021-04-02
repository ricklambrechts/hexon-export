<?php

namespace RoyScheepens\HexonExport;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use RoyScheepens\HexonExport\Controllers\HandleExportController;
use RoyScheepens\HexonExport\Middleware\VerifyIpWhitelist;

use RoyScheepens\HexonExport\Models\Occasion;
use RoyScheepens\HexonExport\Models\OccasionImage;

use RoyScheepens\HexonExport\Observers\OccasionObserver;
use RoyScheepens\HexonExport\Observers\OccasionImageObserver;

use Illuminate\Support\ServiceProvider;

class HexonExportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/hexon-export.php' => config_path('hexon-export.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../migrations' => database_path('migrations')
        ], 'migrations');

        Route::namespace('RoyScheepens\HexonExport\Controllers')
            ->middleware([VerifyIpWhitelist::class])
            ->group(function() {
                Route::post(Config::get('hexon-export.url_endpoint'), [HandleExportController::class, 'handle'])->name('hexon-export.export_handler');
            });

        // Observers
        Occasion::observe(OccasionObserver::class);
        OccasionImage::observe(OccasionImageObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hexon-export.php', 'hexon-export');

        $this->app->bind('hexon-export', function($app) {
            return new HexonExport();
        });
    }
}
