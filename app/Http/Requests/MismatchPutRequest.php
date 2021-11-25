<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MismatchPutRequest extends FormRequest
{

  /**
    *   @OA\Put(
    *       path="/mismatches/{mismatchId}",
    *       operationId="putMismatch",
    *       tags={"store"},
    *       summary="Update Mismatch review status",
    *       @OA\Parameter(
    *          name="mismatchId",
    *          description="The ID of the Mismatch to update",
    *          required=true,
    *          in="path",
    *          @OA\Schema(
    *              type="number"
    *          )
    *       ),
    *       @OA\RequestBody(
    *           description="An object with the new review status field.",
    *           @OA\JsonContent(ref="#/components/schemas/FillableMismatch")
    *       ),
    *       @OA\Response(
    *          response=200,
    *          description="The updated mismatch.",
    *          @OA\JsonContent(ref="#/components/schemas/Mismatch")
    *       ),
    *       @OA\Response(
    *           response=422,
    *           description="Validation errors",
    *           @OA\JsonContent(ref="#/components/schemas/ValidationErrors")
    *       )
    *   )
    *
    */

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
