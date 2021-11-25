<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MismatchResource extends JsonResource
{
       /**
    * @OA\Schema(
    *      schema="Mismatch",
    *      allOf={
    *          @OA\Schema(type="object", properties={
    *              @OA\Property(property="id",type="string"),
    *              @OA\Property(property="item_id",type="string"),
    *              @OA\Property(property="statement_guid",type="string"),
    *              @OA\Property(property="property_id",type="string"),
    *              @OA\Property(property="wikidata_value",type="string"),
    *              @OA\Property(property="external_value",type="string"),
    *              @OA\Property(property="external_url",type="string"),
    *              @OA\Property(property="import",type="object",ref="#/components/schemas/ImportMeta"),
    *              @OA\Property(property="updated_at",type="string",format="date-time"),
    *              @OA\Property(property="reviewer",type="object",ref="#/components/schemas/User")
    *          }),
    *      @OA\Schema(ref="#/components/schemas/FillableMismatch")
    *      }
    * )
    **/
    
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'statement_guid' => $this->statement_guid,
            'property_id' => $this->property_id,
            'wikidata_value' => $this->wikidata_value,
            'external_value' => $this->external_value,
            'external_url' => $this->external_url,
            'review_status' => $this->review_status,
            'reviewer' => new UserResource($this->user),
            'import' => new ImportMetaResource($this->importMeta),
            'updated_at' => $this->updated_at
        ];
    }
}
