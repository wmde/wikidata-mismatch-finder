<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MismatchGetRequest extends FormRequest
{

  /**
    *   @OA\Get(
    *       path="/mismatches/",
    *       operationId="getMismatchesList",
    *       tags={"store"},
    *       summary="Get mismatches by item IDs",
    *       description="Display a listing of the resource",
    *       @OA\Parameter(
    *          name="ids",
    *          description="List of |-separated item IDs to get mismatches for",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *       ),
    *       @OA\Parameter(
    *          name="include_reviewed",
    *          description="Include reviewed mismatches? (default: false)",
    *          required=false,
    *          in="query",
    *          @OA\Schema(
    *              type="boolean"
    *          )
    *       ),
    *       @OA\Parameter(
    *          name="include_expired",
    *          description="Include expired mismatches? (default: false)",
    *          required=false,
    *          in="query",
    *          @OA\Schema(
    *              type="boolean"
    *          )
    *       ),
    *       @OA\Response(
    *          response=200,
    *          description="A list of mismatches, potentially empty if none are found.",
    *          @OA\JsonContent(ref="#/components/schemas/ListOfMismatches")
    *       ),
    *       @OA\Response(
    *           response=422,
    *           description="Validation errors",
    *           @OA\JsonContent(ref="#/components/schemas/ValidationErrors")
    *       )
    *   )
    */

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
