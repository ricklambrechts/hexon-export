<?php

namespace RoyScheepens\HexonExport\Tests;

use CreateOccasionAccessoriesTable;
use CreateOccasionImagesTable;
use CreateOccasionsTable;
use Illuminate\Support\Carbon;
use RoyScheepens\HexonExport\HexonExportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\RayServiceProvider;

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

        // run the up() method of that migration class
        (new CreateOccasionsTable())->up();
        (new CreateOccasionImagesTable())->up();
        (new CreateOccasionAccessoriesTable())->up();
    }

}