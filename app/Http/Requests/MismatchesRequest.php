<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MismatchesRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        $separator = config('mismatches.id_separator');
        $this->replace(['ids' => explode($separator, $this->ids)]);
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
                'max:' . config('mismatches.max_ids')
            ],
            'ids.*' => [
                'regex:/^Q\d+$/'
            ]
        ];
    }
}
