<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImportMetaResource extends JsonResource
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
            'status' => $this->status,
            'description' => $this->description,
            'best_before' => $this->best_before,
            'created' => $this->created_at,
            'uploader' => new UserResource($this->user),
            'links' => [
                'self' => route('imports.show', $this)
            ]
        ];
    }
}
