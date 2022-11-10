<?php

namespace App\Rules;

use App\Services\WikibaseAPIClient;
use Illuminate\Contracts\Validation\Rule;

class MetaWikidataValue implements Rule
{
    /**
     * @var WikibaseAPIClient Wikidata API Client
     */
    private $wikidata;


    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(WikibaseAPIClient $wikidata)
    {
        $this->wikidata = $wikidata;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        [
            'property' => $property,
            'meta_wikidata_value' => $metaWikidataValue
        ] = $value;

        try {
            if (!empty($property) && !empty($metaWikidataValue)) {
                $dataTypes = $this->wikidata->getPropertyDatatypes([$property]);
                return array_key_exists($property, $dataTypes) && $dataTypes[$property] == 'time';
            }
            return empty($metaWikidataValue);
        } catch (\Exception $e) {
            return false;
        }
    }



    public function message()
    {
        return __('validation.meta_wikidata_value');
    }
}
