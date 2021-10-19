<?php

namespace RoyScheepens\HexonExport\Tests\Feature;

use Illuminate\Support\Facades\Config;
use RoyScheepens\HexonExport\Tests\TestCase;

class HandleExportControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setup();
    }

    /**
     * @test
     */
    public function the_route_can_be_accessed(): void
    {
        // Assert that there are no occasions on start
        $this->assertDatabaseCount('hexon_occasions', 0);

        // Load the test data
        $xml = file_get_contents($this->fixturesDir . "/test_car_with_required_information.xml");

        // Post request
        $response = $this->call('POST', route("hexon-export.export_handler"), [], [], [], ['Content-Type' => 'application/xml'], $xml);
        $response->assertOk();

        $content = $response->content();

        self::assertXmlStringEqualsXmlFile($this->fixturesDir . '/result_test_car_with_required_information.xml', trim(explode(PHP_EOL, $content, 6)[5] ?? ''));

        // Assert that there is 1 occasion created
        $this->assertDatabaseCount('hexon_occasions', 1);
    }

    /**
     * @test
     */
    public function the_route_can_be_accessed_with_authentication(): void
    {
        // Assert that there are no occasions on start
        $this->assertDatabaseCount('hexon_occasions', 0);

        // Load the test data
        $xml = file_get_contents($this->fixturesDir . "/test_car_with_required_information.xml");

        Config::set("hexon-export.authentication.enabled", true);
        Config::set("hexon-export.authentication.username", 'admin');
        Config::set("hexon-export.authentication.password", 'password');

        // Post request
        $response = $this->call('POST', route("hexon-export.export_handler"), [], [], [], ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'password'], $xml);
        $response->assertOk();

        $content = $response->content();

        self::assertXmlStringEqualsXmlFile($this->fixturesDir . '/result_test_car_with_required_information.xml', trim(explode(PHP_EOL, $content, 6)[5] ?? ''));

        Config::set("hexon-export.authentication.enabled", false);

        // Assert that there is 1 occasion created
        $this->assertDatabaseCount('hexon_occasions', 1);
    }
}