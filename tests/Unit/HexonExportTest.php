<?php

namespace RoyScheepens\HexonExport\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RoyScheepens\HexonExport\Facades\HexonExport;
use RoyScheepens\HexonExport\Tests\TestCase;

class HexonExportTest extends TestCase
{

    /** @test */
    public function it_should_not_save_the_xml_by_default(): void
    {
        // Load the test data
        $xml = simplexml_load_string(file_get_contents($this->fixturesDir . "/test_car_without_information.xml"));

        // Handle the export file
        HexonExport::handle($xml);

        // Generate saved export file name
        $filename = $this->getSavedExportFilename(12345678);

        // Assert the export file exists
        Storage::disk('local')->assertMissing("hexon-export/xml/" . $filename);
    }

    /** @test */
    public function it_should_save_the_xml_when_enabled(): void
    {
        // Set store_xml to true
        Config::set('hexon-export.store_xml', true);

        // Load the test data
        $xml = simplexml_load_string(file_get_contents($this->fixturesDir . "/test_car_without_information.xml"));

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
        $this->assertDatabaseCount('hexon_occasions', 0);

        // Load the test data
        $xml = simplexml_load_string(file_get_contents($this->fixturesDir . "/test_car_without_information.xml"));

        // Handle the export file
        $result = HexonExport::handle($xml);

        // Assert that there is an error because there are no images in the xml
        self::assertTrue($result->hasErrors());
        self::assertContains("No images supplied, cannot proceed.", $result->getErrors());

        $this->assertDatabaseCount('hexon_occasions', 0);
    }

    /** @test */
    public function it_should_return_an_error_when_resource_has_no_brand(): void
    {
        $this->assertDatabaseCount('hexon_occasions', 0);

        // Load the test data
        $xml = simplexml_load_string(file_get_contents($this->fixturesDir . "/test_car_with_images.xml"));

        // Handle the export file
        $result = HexonExport::handle($xml);

        // Assert that there is an error because there are no images in the xml
        self::assertTrue($result->hasErrors());
        self::assertContains("No brand supplied, cannot proceed.", $result->getErrors()) ;

        $this->assertDatabaseCount('hexon_occasions', 0);
    }

    /** @test */
    public function it_should_return_an_error_when_resource_has_no_model(): void
    {
        $this->assertDatabaseCount('hexon_occasions', 0);

        // Load the test data
        $xml = simplexml_load_string(file_get_contents($this->fixturesDir . "/test_car_with_images_brand.xml"));

        // Handle the export file
        $result = HexonExport::handle($xml);

        // Assert that there is an error because there are no images in the xml
        self::assertTrue($result->hasErrors());
        self::assertContains("No model supplied, cannot proceed.", $result->getErrors()) ;

        $this->assertDatabaseCount('hexon_occasions', 0);
    }

    /** @test */
    public function it_should_create_an_new_occasion(): void
    {
        $this->assertDatabaseCount('hexon_occasions', 0);

        // Load the test data
        $xml = simplexml_load_string(file_get_contents($this->fixturesDir . "/test_car_with_required_information.xml"));

        // Handle the export file
        HexonExport::handle($xml);

        $this->assertDatabaseCount('hexon_occasions', 1);
    }

    private function getSavedExportFilename($hexonNumber)
    {
        return str_replace([":", " "], ["-", "_"], now()->toDateTimeString() . '_' . $hexonNumber . '.xml');
    }
}