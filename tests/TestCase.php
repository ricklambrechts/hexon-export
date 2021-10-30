<?php

namespace RoyScheepens\HexonExport\Tests;

use AddBrandSlugToOccasionsTable;
use AddCustomerNumberToOccasionsTable;
use AddDealerFieldsToOccasionsTable;
use AddModelSlugToOccasionsTable;
use AddVehicleTypeToOccasionsTable;
use CreateOccasionAccessoriesTable;
use CreateOccasionImagesTable;
use CreateOccasionsTable;
use Illuminate\Support\Carbon;
use RoyScheepens\HexonExport\HexonExportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\RayServiceProvider;
use UpdateFuelTypeInOccasionsTable;

abstract class TestCase extends Orchestra
{
    protected string $fixturesDir = __DIR__ . '/fixtures';

    public function setUp(): void
    {
        parent::setup();

        $this->setUpDatabase();

        Carbon::setTestNow();
    }

    protected function getPackageProviders($app): array
    {
        return [
            HexonExportServiceProvider::class,
            RayServiceProvider::class
        ];
    }

    protected function setUpDatabase(): void
    {
        include_once __DIR__ . '/../database/migrations/create_occasions_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_occasion_images_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_occasion_accessories_table.php.stub';
        include_once __DIR__ . '/../database/migrations/add_customer_number_to_occasions_table.php.stub';
        include_once __DIR__ . '/../database/migrations/add_vehicle_type_to_occasions_table.php.stub';
        include_once __DIR__ . '/../database/migrations/add_brand_slug_to_occasions_table.php.stub';
        include_once __DIR__ . '/../database/migrations/add_model_slug_to_occasions_table.php.stub';
        include_once __DIR__ . '/../database/migrations/update_fuel_type_in_occasions_table.php.stub';
        include_once __DIR__ . '/../database/migrations/add_dealer_fields_to_occasions_table.php.stub';

        // run the up() method of that migration class
        (new CreateOccasionsTable())->up();
        (new CreateOccasionImagesTable())->up();
        (new CreateOccasionAccessoriesTable())->up();
        (new AddCustomerNumberToOccasionsTable())->up();
        (new AddVehicleTypeToOccasionsTable())->up();
        (new AddBrandSlugToOccasionsTable())->up();
        (new AddModelSlugToOccasionsTable())->up();
        (new UpdateFuelTypeInOccasionsTable())->up();
        (new AddDealerFieldsToOccasionsTable())->up();
    }

}