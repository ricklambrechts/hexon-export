<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hexon IP Whitelist
    |--------------------------------------------------------------------------
    |
    | The IP addresses Hexon uses to POST data to your server are whitelisted
    | here. The whitelist is only checked when in production.
    |
    */

    'ip_whitelist' => [
        '82.94.237.8',
        '82.94.240.8',
        '82.148.219.135',
    ],

    'authentication' => [
        'enabled' => env('HEXON_AUTH_ENABLED', false),
        'username' => env('HEXON_AUTH_USERNAME', ''),
        'password' => env('HEXON_AUTH_PASSWORD', ''),
    ],

    /*
     |--------------------------------------------------------------------------
     | Url Endpoint
     |--------------------------------------------------------------------------
     |
     | The url where the POST requests from Hexon are routed to.
     | You could leave this empty if you don't want to register the route.
     |
     */
    'url_endpoint' => '/hexon-export',

    /*
     |--------------------------------------------------------------------------
     | Images Storage Path
     |--------------------------------------------------------------------------
     |
     | The path where occasion images, relative to your 'public' storage disk.
     |
     */
    'images_storage_path' => 'occasions/images/',

    /*
     |--------------------------------------------------------------------------
     | Store XML
     |--------------------------------------------------------------------------
     |
     | A boolean that defines if the received xml should be stored
     |
     */
    'store_xml' => false,

    /*
     |--------------------------------------------------------------------------
     | XML Storage Path
     |--------------------------------------------------------------------------
     |
     | The path where incoming XML files are stored, relative to
     | your 'default' storage disk.
     |
     */
    'xml_storage_path' => 'hexon-export/xml/',
];
