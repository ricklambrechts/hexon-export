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
        $this->registerPublishables();

        $this->registerRoutes();

        // Observers
        Occasion::observe(OccasionObserver::class);
        OccasionImage::observe(OccasionImageObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hexon-export.php', 'hexon-export');

        $this->app->bind('hexon-export', function () {
            return new HexonExport();
        });
    }

    private function registerPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../config/hexon-export.php' => config_path('hexon-export.php'),
        ], 'config');

        if (! class_exists('CreateOccasionsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_occasions_table.php.stub' => database_path('migrations/'.date('Y_m_d').'_000000_create_occasions_table.php'),
            ], 'migrations');
        }

        if (! class_exists('CreateOccasionImagesTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_occasion_images_table.php.stub' => database_path('migrations/'.date('Y_m_d').'_100000_create_occasion_images_table.php'),
            ], 'migrations');
        }

        if (! class_exists('CreateOccasionAccessoriesTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_occasion_accessories_table.php.stub' => database_path('migrations/'.date('Y_m_d').'_200000_create_occasion_accessories_table.php'),
            ], 'migrations');
        }

        if (! class_exists('AddCustomerNumberToOccasionsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/add_customer_number_to_occasions_table.php.stub' => database_path('migrations/'.date('Y_m_d').'_300000_add_customer_number_to_occasions_table.php'),
            ], 'migrations');
        }

        if (! class_exists('AddVehicleTypeToOccasionsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/add_vehicle_type_to_occasions_table.php.stub' => database_path('migrations/'.date('Y_m_d').'_400000_add_vehicle_type_to_occasions_table.php'),
            ], 'migrations');
        }

        if (! class_exists('AddBrandSlugToOccasionsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/add_brand_slug_to_occasions_table.php.stub' => database_path('migrations/'.date('Y_m_d').'_500000_add_brand_slug_to_occasions_table.php'),
            ], 'migrations');
        }

        if (! class_exists('AddModelSlugToOccasionsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/add_model_slug_to_occasions_table.php.stub' => database_path('migrations/'.date('Y_m_d').'_510000_add_model_slug_to_occasions_table.php'),
            ], 'migrations');
        }

        if (! class_exists('UpdateFuelTypeInOccasionsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/update_fuel_type_in_occasions_table.php.stub' => database_path('migrations/'.date('Y_m_d').'_600000_update_fuel_type_in_occasions_table.php'),
            ], 'migrations');
        }
    }

    private function registerRoutes(): void
    {
        $endpoint = Config::get('hexon-export.url_endpoint');
        if (empty($endpoint)) {
            return;
        }

        Route::post($endpoint, [HandleExportController::class, 'handle'])
            ->middleware([VerifyIpWhitelist::class])
            ->name('hexon-export.export_handler');
    }
}
