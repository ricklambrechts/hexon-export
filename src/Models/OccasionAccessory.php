<?php

namespace RoyScheepens\HexonExport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OccasionAccessory extends Model
{
    /**
     * The table name
     * todo: make this a config setting
     * @var string
     */
    protected $table = 'hexon_occasion_accessories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<array-key, string>
     */
    protected $fillable = [
        'occasion_id',
        'name'
    ];

    /**
     * Relations
     * ----------------------------------------
     */

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }
}
