<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request to provide several mismatch reviews in bulk
 *
 * This form request holds validation rules for the bulk review
 * of mismatches
 */
class BulkReviewsPutRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '*' => 'array',
            '*.id' => 'required|integer',
            '*.review_status' => [
                'required',
                'in:' . implode(',', config('mismatches.validation.review_status.accepted_values'))
             ]
        ];
    }
}
