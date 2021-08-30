<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MismatchGetRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        // split ids parameter into array, if it is there
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
