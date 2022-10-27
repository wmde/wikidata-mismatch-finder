<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Services\WikibaseAPIClient;
use App\Exceptions\WikibaseValueParserException;

class WikidataValue implements Rule
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
            'value' => $wikidataValue
        ] = $value;

        try {
            // TODO would be nice to validate batches of values at once
            $this->wikidata->parseValues([$property => [$wikidataValue]]);
        } catch (WikibaseValueParserException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.wikidata_value');
    }
}
