<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request to provide a single mismatch review
 *
 * This form request holds validation rules for the review
 * of a single mismatch
 */
class MismatchPutRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        // make route parameter available for validation
        $this->merge(['id' => $this->route('mismatch')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'numeric',
            'review_status' => [
                'required',
                'in:' . implode(',', config('mismatches.validation.review_status.accepted_values'))
            ],
            'statement_guid' => 'prohibited',
            'property_id' => 'prohibited',
            'wikidata_value' => 'prohibited',
            'external_value' => 'prohibited',
            'external_url' => 'prohibited',
        ];
    }
}
