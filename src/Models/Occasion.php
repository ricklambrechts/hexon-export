<?php

namespace RoyScheepens\HexonExport\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Occasion extends Model
{
    /**
     * The table name
     * todo: make this a config setting
     * @var string
     */
    protected $table = 'hexon_occasions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<array-key, string>|bool
     */
    protected $guarded = [
        'id'
    ];

    /**
     * The attributes that are appended to the model
     *
     * @var array
     */
    protected $appends = [
        'name',
        'price_formatted',
        // 'description',
    ];

    /**
     * Which attributes to parse as dates
     *
     * @var array
     */
    protected $dates = [
        'apk_until',
        'sold_at'
    ];

    /**
     * Which attributes to cast
     *
     * @var array
     */
    protected $casts = [
        'sold' => 'boolean'
    ];

    /**
     * Route Binding
     * ----------------------------------------
     */

    public function getRouteKeyName()
    {
        // todo: make configurable
        return 'slug';
    }

    /**
     * Relations
     * ----------------------------------------
     */

    public function image(): HasOne
    {
        return $this->hasOne(OccasionImage::class)->orderBy('id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(OccasionImage::class);
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(OccasionAccessory::class)->orderBy('name');
    }

    /**
     * Attributes
     * ----------------------------------------
     */

    /**
     * Get the name of the Ocassion
     * @return string
     * @psalm-suppress InaccessibleProperty
     */
    public function getNameAttribute(): string
    {
        return implode(' ', [
            $this->brand,
            $this->model
        ]);
    }

    /**
     * Get the full name of the Ocassion
     * @return string
     * @psalm-suppress InaccessibleProperty
     */
    public function getNameFullAttribute(): string
    {
        return implode(' ', [
            $this->brand,
            $this->model,
            $this->type
        ]);
    }

    public function getApkUntilFormattedAttribute(): string
    {
        return $this->apk_until->format('d-m-Y');
    }

    public function getPriceFormattedAttribute(): string
    {
        return '€ ' . number_format($this->price, 0, ',', '.') . ',-';
    }

    public function getRemarksAttribute(string $val): string
    {
        return html_entity_decode($val, ENT_QUOTES | ENT_HTML5);
    }

    public function getLicensePlateFormattedAttribute(): ?string
    {
        if (! $this->license_plate) {
            return null;
        }

        $formatted = '';

        foreach (str_split($this->license_plate) as $char) {
            $type = is_numeric($char) ? 'number' : 'string';
            $prevChar = substr($formatted, -1);

            if ($prevChar == '') {
                $formatted .= $char;
                continue;
            }

            $prevCharType = is_numeric($prevChar) ? 'number' : 'string';

            if ($type != $prevCharType) {
                $formatted .= '-';
            }

            $formatted .= $char;
        }

        return $formatted;
    }

    public function getColorFormattedAttribute(): string
    {
        return ucwords(mb_strtolower($this->color));
    }

    public function getMileageFormattedAttribute(): string
    {
        $units = [
            'K' => 'km',
            'M' => 'm'
        ];

        return number_format($this->mileage, 0, ',', '.') . ' ' . $units[$this->mileage_unit];
    }

    public function getVatMarginFormattedAttribute(): string
    {
        return $this->vat_margin === 'M' ? 'Marge' : 'BTW';
    }

    public function getVehicleTaxFormattedAttribute(): string
    {
        return '€ ' . number_format($this->vehicle_tax, 0, ',', '.') . ',-';
    }

    public function getDeliveryCostsFormattedAttribute(): string
    {
        return '€ ' . number_format($this->delivery_costs, 0, ',', '.') . ',-';
    }

    public function getRoadTaxMinFormattedAttribute(): string
    {
        return '€ ' . number_format($this->road_tax_min, 0, ',', '.') . ',-';
    }

    public function getRoadTaxMaxFormattedAttribute(): string
    {
        return '€ ' . number_format($this->road_tax_max, 0, ',', '.') . ',-';
    }

    public function getFuelTypeFormattedAttribute(): string
    {
        $types = [
            'B' => 'Benzine',
            'D' => 'Diesel',
            'L' => 'LPG',
            '3' => '', // todo
            'E' => 'Elektrisch',
            'H' => 'Waterstof',
            'C' => '', // todo
            'O' => '', // todo
        ];

        return $types[$this->fuel_type];
    }

    public function getTransmissionFormattedAttribute(): string
    {
        $types = [
            'H' => 'Handgeschakeld',
            'A' => 'Automaat',
            'S' => 'Sequentieel',
            'C' => '' // todo
        ];

        return $types[$this->transmission];
    }

    public function getMassFormattedAttribute(): string
    {
        return number_format($this->mass, 0, ',', '.') . 'kg';
    }

    public function getCylinderCapacityFormattedAttribute(): string
    {
        return $this->cylinder_capacity . ' cc';
    }

    public function getPowerAttribute(): ?string
    {
        if ($this->power_hp && $this->power_kw) {
            return sprintf("%d pk / %d Kw", $this->power_hp, $this->power_kw);
        }

        if ($this->power_hp) {
            return sprintf("%d pk", $this->power_hp);
        }

        if ($this->power_kw) {
            return sprintf("%d Kw", $this->power_kw);
        }

        return null;
    }

    public function getCo2EmissionFormattedAttribute(): string
    {
        return $this->co2_emission . ' g/Km';
    }

    public function getFuelConsumptionCityFormattedAttribute(): string
    {
        return $this->fuel_consumption_city . ' l/100 Km';
    }

    public function getFuelConsumptionHighwayFormattedAttribute(): string
    {
        return $this->fuel_consumption_highway . ' l/100 Km';
    }

    public function getFuelConsumptionAvgFormattedAttribute(): string
    {
        return $this->fuel_consumption_avg . ' l/100 Km';
    }

    public function getRoadTaxAttribute(): ?string
    {
        if ($this->road_tax_min && $this->road_tax_max) {
            return sprintf(
                "€ %s - € %s p/kw",
                number_format($this->road_tax_min, 0, ',', '.'),
                number_format($this->road_tax_max, 0, ',', '.')
            );
        }

        return null;
    }

    /**
     * Scopes
     * ----------------------------------------
     */

    /**
     * Returns only occasions that are sold
     * @param Builder $query The query builder instance
     * @return Builder
     */
    public function scopeSold(Builder $query): Builder
    {
        return $query->where('sold', true);
    }

    /**
     * Returns only occasions that are not sold
     * @param Builder $query The query builder instance
     * @return Builder
     */
    public function scopeNotSold(Builder $query): Builder
    {
        return $query->where('sold', false);
    }
}
