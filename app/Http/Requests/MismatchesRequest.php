<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MismatchesRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        if ($this->ids) {
            $separator = config('mismatches.id_separator');
            $this->merge(['ids' => explode($separator, $this->ids)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->isMethod('get')) {
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
        } elseif ($this->isMethod('put')) {
            $review_status_values = implode(config('mismatches.validation.review_status.accepted_values'));
            return [
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
}
