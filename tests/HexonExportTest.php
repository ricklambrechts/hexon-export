<?php

namespace RoyScheepens\HexonExport\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;
use RoyScheepens\HexonExport\Facades\HexonExport;
use RoyScheepens\HexonExport\HexonExportServiceProvider;

class HexonExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setup();

        Carbon::setTestNow();
    }

    protected function getPackageProviders($app): array
    {
        return [
            HexonExportServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /** @test */
    public function it_should_save_the_xml(): void
    {
        // Load the test data
        $xml = simplexml_load_string(file_get_contents(__DIR__ . "/fixtures/test_car_without_information.xml"));

        // Handle the export file
        HexonExport::handle($xml);

        // Generate saved export file name
        $filename = $this->getSavedExportFilename(12345678);

        // Assert the export file exists
        Storage::disk('local')->assertExists("hexon-export/xml/" . $filename);

        // Load the export file
        $newXml = simplexml_load_string(Storage::disk('local')->get("hexon-export/xml/" . $filename));

        // Assert if the the received xml is equal to the saved xml
        self::assertSame($xml->asXML(), $newXml->asXML());
    }

    /** @test */
    public function it_should_return_an_error_when_resource_has_no_images(): void
    {
        $xml = simplexml_load_string(file_get_contents(__DIR__ . "/fixtures/test_car_without_information.xml"));

        $result = HexonExport::handle($xml);

        self::assertTrue($result->hasErrors());
        self::assertContains("No images supplied, cannot proceed.", $result->getErrors()) ;
    }

    private function getSavedExportFilename($hexonNumber)
    {
        return str_replace([":", " "], ["-", "_"], now()->toDateTimeString() . '_' . $hexonNumber . '.xml');
    }
}