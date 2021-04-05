<?php

namespace RoyScheepens\HexonExport\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OccasionImage extends Model
{
    /**
     * The table name
     * todo: make this a config setting
     * @var string
     */
    protected $table = 'hexon_occasion_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<array-key, string>
     */
    protected $fillable = [
        'occasion_id',
        'resource_id',
        'filename'
    ];

    /**
     * The attributes that are appended to the model
     * @var array
     */
    protected $appends = [
        'path',
        'url'
    ];

    /**
     * Relations
     * ----------------------------------------
     */

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    /**
     * Attributes
     * ----------------------------------------
     */

    public function getPathAttribute(): string
    {
        return implode('/', [
            config('hexon-export.images_storage_path') . $this->occasion->resource_id,
            $this->filename
        ]);
    }

    /**
     * Get the public url of the image
     * @return string
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
