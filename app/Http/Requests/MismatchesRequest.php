<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MismatchesRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        $separator = config('mismatches.id_separator');
        $this->merge(['ids' => explode($separator, $this->ids)]);
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
            return [
                'review_status' => [
                    'required',
                    'in:pending,wikidata,external,both,none'
                ],
                'statetement_guid' => 'prohibited',
                'property_id' => 'prohibited',
                'wikidata_value' => 'prohibited',
                'external_value' => 'prohibited',
                'external_url' => 'prohibited',
            ];
        }
    }
}
