<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MismatchResource extends JsonResource
{
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
            'meta_wikidata_value' => $this->meta_wikidata_value,
            'external_value' => $this->external_value,
            'external_url' => $this->external_url,
            'review_status' => $this->review_status,
            'reviewer' => new UserResource($this->user),
            'import' => new ImportMetaResource($this->importMeta),
            'updated_at' => $this->updated_at
        ];
    }
}
