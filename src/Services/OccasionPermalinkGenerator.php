<?php

namespace RoyScheepens\HexonExport\Services;

use RoyScheepens\HexonExport\Contracts\PermalinkGenerator;
use RoyScheepens\HexonExport\Models\Occasion;

class OccasionPermalinkGenerator implements PermalinkGenerator
{

    public function generate(Occasion $occasion): string
    {
        return '';
    }

}