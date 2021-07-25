<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
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
            "id" => $this->id,
            "name" => $this->name,
            "surname" => $this->surname,
            "phone_number" => $this->phone_number,
            "picture" => $this->getProfilePath(),
            "dob" => $this->dob,
            "village" => $this->village,
            "city" => $this->city,
            "province" => $this->province,
            "created_at" => $this->created_at,
            "role" => $this->roles[0]->display_name,
        ];
    }
}
