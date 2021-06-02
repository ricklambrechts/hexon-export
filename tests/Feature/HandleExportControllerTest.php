<?php

namespace RoyScheepens\HexonExport\Tests\Feature;

use Exception;
use Illuminate\Support\Facades\Config;
use RoyScheepens\HexonExport\Tests\TestCase;

class HandleExportControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setup();

//        Config::set("hexon-export.url_endpoint", "");
    }

    /**
     * @test
     * @throws Exception
     */
    public function the_route_can_be_accessed(): void
    {
        // Assert that there are no occasions on start
        $this->assertDatabaseCount('hexon_occasions', 0);

        // Load the test data
        $xml = file_get_contents($this->fixturesDir . "/test_car_with_required_information.xml");

        // Post request
        $this->call('POST', route("hexon-export.export_handler"), [], [], [], [], $xml)
            ->assertOk()
            ->assertSeeText("1"); // ->assertLocation("/hexon-export")

        // Assert that there is 1 occasion created
        $this->assertDatabaseCount('hexon_occasions', 1);
    }

    // TODO: Fix this error
//    /**
//     * @test
//     * @throws Exception
//     */
//    public function the_route_cannot_be_accessed_when_not_set(): void
//    {
//        Config::set("hexon-export.url_endpoint", "");
//
//        // Load the test data
//        $xml = file_get_contents($this->fixturesDir . "/test_car_with_required_information.xml");
//
//        // Post request
//        $this->call('POST', route("hexon-export.export_handler"), [], [], [], [], $xml)
//            ->assertNotFound();
//
//        // Assert that there is 1 occasion created
//        $this->assertDatabaseCount('hexon_occasions', 0);
//    }
}