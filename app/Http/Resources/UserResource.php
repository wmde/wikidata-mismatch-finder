<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @OA\Schema(
     *      schema="User",
     *      @OA\Property(property="id",type="number"),
     *      @OA\Property(property="username",type="string"),
     *      @OA\Property(property="mw_userid",type="number")
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
            'username' => $this->username,
            'mw_userid' => $this->mw_userid
        ];
    }
}
