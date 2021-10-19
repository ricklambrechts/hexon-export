<?php

namespace RoyScheepens\HexonExport\Contracts;

use RoyScheepens\HexonExport\Models\Occasion;

interface PermalinkGenerator
{
    public function generate(Occasion $occasion): string;
}