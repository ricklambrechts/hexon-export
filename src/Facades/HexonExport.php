<?php

namespace RoyScheepens\HexonExport\Facades;

use Illuminate\Support\Facades\Facade;
use SimpleXMLElement;

/**
 * @method static \RoyScheepens\HexonExport\HexonExport handle(SimpleXmlElement $xml)
 *
 * @see \RoyScheepens\HexonExport\HexonExport
 */
class HexonExport extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'hexon-export';
    }
}
