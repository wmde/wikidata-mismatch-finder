<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request to retrieve mismatches
 *
 * This form request holds validation rules and a pre-process
 * to split the provided item ids parameter string into an array
 */
class MismatchGetRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        // sanitise 'ids' parameter and split into array
        if ($this->ids) {
            $sanitisedIds = strtoupper($this->ids);
            $separator = config('mismatches.id_separator');
            $this->merge(['ids' => explode($separator, $sanitisedIds)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ids' => [
                'required',
                'array',
                'max:' . config('mismatches.validation.ids.max')
            ],
            'ids.*' => [
                'required',
                'regex:' . config('mismatches.validation.item_id.format'),
                'max:' . config('mismatches.validation.item_id.max_length'),
            ]
        ];
    }
}
