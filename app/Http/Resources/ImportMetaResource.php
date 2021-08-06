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
        $meta = [
            'id' => $this->id,
            'status' => $this->status,
            'description' => $this->description,
            'expires' => $this->expires,
            'created' => $this->created_at,
            'uploader' => new UserResource($this->user),
            'links' => [
                'self' => route('imports.show', $this)
            ]
        ];

        if ($this->error) {
            $meta['error'] = $this->error->message;
        }

        return $meta;
    }
}
