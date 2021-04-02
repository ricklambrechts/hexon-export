<?php

namespace RoyScheepens\HexonExport\Facades;

use Illuminate\Support\Facades\Facade;

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