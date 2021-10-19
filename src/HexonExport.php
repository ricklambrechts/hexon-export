<?php

namespace RoyScheepens\HexonExport;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RoyScheepens\HexonExport\Models\Occasion;

use SimpleXmlElement;

use Carbon\Carbon;

class HexonExport
{

    /**
     * The Hexon Id of the resource
     * @var int|null
     */
    protected ?int $resourceId = null;

    /**
     * The Hexon customer number of the resource
     * @var int|null
     */
    protected ?int $customerNumber = null;

    /**
     * The local resource we are going to create or update
     * @var Occasion|null
     */
    protected ?Occasion $resource = null;

    /**
     * Array of errors
     * @var array
     */
    protected array $errors = [];

    /**
     * Class Constructor
     */
    public function __construct()
    {
        // todo: add option to set image disk on storage
    }

    /**
     * Handles the import of the XML
     *
     * @param SimpleXmlElement $xml
     * @return HexonExport
     * @throws Exception
     */
    public function handle(SimpleXmlElement $xml): HexonExport
    {
        // The resource id from Hexon
        $this->resourceId = (int) $xml->voertuignr_hexon;

        // Store the XML to disk before processing
        $this->saveXml($xml);

        // Perform an insert/update or delete, based on the action supplied
        switch ($xml->attributes()->actie ?? '') {
            // Inserts or updates the existing record
            case 'add':
            case 'change':

                // Validate the resource
                if (!$this->isValid($xml)) {
                    return $this;
                }

                // todo: set version check (2.12)
                // $xml->attributes()->versie

                try {

                    // Get the existing resource or create it with the resourceId
                    $this->resource = Occasion::withoutGlobalScopes()->where('resource_id', $this->resourceId)->firstOrNew([
                        'resource_id' => $this->resourceId
                    ]);

                    // Set all attributes and special properties of the resource
                    $this->setAttribute('customer_number', $xml->klantnummer);
                    $this->customerNumber = (int) $xml->klantnummer;

                    $this->setAttribute('brand', $xml->merk);
                    $this->setAttribute('model', $xml->model);
                    $this->setAttribute('type', $xml->type);
                    $this->setAttribute('license_plate', $xml->kenteken);
                    if ($xml->apk) {
                        $this->setAttribute('apk_until', $xml->apk->attributes()->tot, 'date');
                    }

                    $this->setAttribute('bodywork', $xml->carrosserie);
                    $this->setAttribute('vehicle_type', $xml->voertuigsoort);
                    $this->setAttribute('color', $xml->kleur);
                    $this->setAttribute('base_color', $xml->basiskleur);
                    $this->setAttribute('lacquer', $xml->laktint);
                    $this->setAttribute('lacquer_type', $xml->laksoort);
                    $this->setAttribute('num_doors', $xml->aantal_deuren, 'int');
                    $this->setAttribute('num_seats', $xml->aantal_zitplaatsen, 'int');

                    $this->setAttribute('fuel_type', $xml->brandstof);
                    $this->setAttribute('mileage', $xml->tellerstand, 'int');
                    if ($xml->tellerstand) {
                        $this->setAttribute('mileage_unit', $xml->tellerstand->attributes()->eenheid);
                    }
                    $this->setAttribute('range', $xml->actieradius, 'int');

                    $this->setAttribute('transmission', $xml->transmissie);
                    $this->setAttribute('num_gears', $xml->aantal_versnellingen, 'int');

                    $this->setAttribute('mass', $xml->massa, 'int');
                    $this->setAttribute('max_towing_weight', $xml->max_trekgewicht, 'int');
                    $this->setAttribute('payload', $xml->laadvermogen, 'int');
                    $this->setAttribute('num_cylinders', $xml->cilinder_aantal, 'int');
                    $this->setAttribute('cylinder_capacity', $xml->cilinder_inhoud, 'int');

                    $this->setAttribute('power_hp', $xml->vermogen_motor_pk, 'int');
                    $this->setAttribute('power_kw', $xml->vermogen_motor_kw, 'int');

                    $this->setAttribute('top_speed', $xml->topsnelheid);

                    $this->setAttribute('fuel_capacity', $xml->tankinhoud, 'int');
                    $this->setAttribute('fuel_consumption_avg', $xml->gemiddeld_verbruik ?? null, 'float');
                    $this->setAttribute('fuel_consumption_city', $xml->verbruik_stad ?? null, 'float');
                    $this->setAttribute('fuel_consumption_highway', $xml->verbruik_snelweg ?? null, 'float');
                    $this->setAttribute('co2_emission', $xml->co2_uitstoot);
                    $this->setAttribute('energy_label', $xml->energie_label);

                    $this->setAttribute('remarks', $xml->opmerkingen);

                    $this->setAttribute('vat_margin', $xml->btw_marge);
                    $this->setAttribute('vehicle_tax', $xml->bpm_bedrag, 'int');
                    if ($xml->wegenbelasting_kwartaal) {
                        $this->setAttribute('road_tax_min', $xml->wegenbelasting_kwartaal->attributes()->min, 'int');
                        $this->setAttribute('road_tax_max', $xml->wegenbelasting_kwartaal->attributes()->max, 'int');
                    }
                    $this->setAttribute('delivery_costs', $xml->kosten_rijklaar, 'int');

                    $this->setAttribute('price', $xml->verkoopprijs_particulier, 'int');

                    $this->setAttribute('sold', (string) $xml->verkocht === 'j', 'boolean');
                    $this->setAttribute('sold_at', $xml->verkocht_datum, 'date');

                    // Sets the build year
                    $this->setBuildYear($xml->datum_deel_1);

                    // Save the resource to the database, so we can start
                    $this->resource->save();

                    // Sets the accessories
                    $this->setAccessories($xml->accessoires->accessoire);

                    // Set the images
                    $this->setImages($xml->afbeeldingen->afbeelding);
                } catch (Exception $e) {
                    $this->setError('Unable to save or update resource.');

                    $this->setError($e->getMessage());
                }

                break;

            // Deletes the resource and all associated data
            case 'delete':

                $this->resource = Occasion::withoutGlobalScopes()->where('resource_id', $this->resourceId)->first();
                if ($this->resource) {
                    $this->resource->delete();
                }

                break;

            // Nothing to do here...
            default:
                break;
        }

        return $this;
    }

    /**
     * Sets an attribute to the resource and casts to desired type
     * @param string $attr The attribut key to set
     * @param mixed $value The value
     * @param string $type To which type to cast
     * @param null $fallback
     */
    protected function setAttribute(string $attr, $value, $type = 'string', $fallback = null): void
    {
        if ($this->resource === null) {
            return;
        }

        switch ($type) {
            case 'int':
                $value = (int) $value;
                break;

            case 'string':
                $value = (string) $value;
                break;

            case 'boolean':
                $value = $value ? true : false;
                break;

            // Try to parse as a Carbon object, if it fails set it to the fallback value
            case 'date':
                try {
                    $value = Carbon::createFromFormat('d-m-Y', $value);
                } catch (Exception $e) {
                    $value = $fallback;
                }

                break;
        }

        // Use the fallback value should it be empty
        if ($type !== 'boolean' && empty($value)) {
            $value = $fallback;
        }

        $this->resource->setAttribute($attr, $value);
    }

    /**
     * Sets the build_year attribute based on the datum_deel_1 value
     *
     * @param string|null $registrationDate
     * @return void
     */
    private function setBuildYear(?string $registrationDate): void
    {
        if ($this->resource === null) {
            return;
        }

        if ($registrationDate && $date = Carbon::createFromFormat('d-m-Y', $registrationDate)) {
            $this->resource->setAttribute('build_year', $date->format('m-Y'));
        }
    }

    /**
     * Sets the accessories
     *
     * @param array $accessories
     * @return void
     */
    protected function setAccessories($accessories): void
    {
        if (!$accessories) {
            return;
        }

        if ($this->resource === null) {
            return;
        }

        // First, remove all accessories
        $this->resource->accessories()->delete();

        foreach ($accessories as $accessory) {
            $name = (string) $accessory->naam;

            if (
                empty($name) ||
                strlen($name) <= 1 ||
                in_array(substr($name, 0, 1), ['(', ')', '&'])
            ) {
                continue;
            }

            $this->resource->accessories()->create([
                'name' => Str::limit($name, 160)
            ]);
        }
    }

    /**
     * Stores the images to disk
     * @param  array $images An array of images
     * @return void
     */
    protected function setImages($images): void
    {
        if (!$images) {
            return;
        }

        if ($this->resource === null) {
            return;
        }


        $this->resource->images()->delete();

        foreach ($images as $image) {
            $imageId = (int) $image->attributes()->nr;
            $imageUrl = (string) $image->url;

            try {
                $contents = @file_get_contents($imageUrl);
                if (!$contents) {
                    continue;
                }

                $filename = implode('_', [
                        $this->resourceId,
                        $imageId
                    ]).'.jpg';

                $imageResource = $this->resource->images()->create([
                    'resource_id' => $this->resourceId,
                    'filename' => $filename
                ]);

                // Use the path attribute to set as the file destination
                Storage::disk('public')->put($imageResource->path, $contents);

                $imageResource->save();
            } catch(Exception $ignore) {
                continue;
                // todo: handle exception?
            }
        }
    }

    /**
     * Stores the XML to disk
     * @param SimpleXmlElement $xml The XML data to write to disk
     * @return void
     */
    protected function saveXml(SimpleXmlElement $xml): void
    {
        if ($this->resourceId === null) {
            return;
        }

        if (config('hexon-export.store_xml') === false || empty(config('hexon-export.xml_storage_path'))) {
            return;
        }

        $filename = str_replace([":", " "], ["-", "_"], now()->toDateTimeString() . '_' . $this->resourceId.'.xml');

        $xmlData = $xml->asXML();
        if (!$xmlData) {
            return;
        }
        Storage::put(config('hexon-export.xml_storage_path') . $filename, $xmlData);
    }

    /**
     * Set an error
     * @param string $err The error description
     */
    protected function setError(string $err): void
    {
        $this->errors[] = $err;
    }

    /**
     * Do we have any errors?
     * @return boolean True if we do, false if not
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Returns the errors
     * @return array Array of errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getResourceId(): ?int
    {
        return $this->resourceId;
    }

    public function getCustomerNumber(): ?int
    {
        return $this->customerNumber;
    }

    public function getResource(): ?Occasion
    {
        return $this->resource;
    }

    private function isValid(SimpleXmlElement $xml): bool
    {
        if (empty($xml->afbeeldingen)) {
            $this->setError('No images supplied, cannot proceed.');
            return false;
        }

        // Check if the resource has a brand
        if (empty($xml->merk)) {
            $this->setError('No brand supplied, cannot proceed.');
            return false;
        }

        // Check if the resource has a model
        if (empty($xml->model)) {
            $this->setError('No model supplied, cannot proceed.');
            return false;
        }

        return true;
    }
}
