<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfile extends JsonResource
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
            'id'=>$this->id,
            'name'=>$this->name,
            'surname'=>$this->surname,
            'phone_number'=>$this->phone_number,
            'picture'=>$this->getProfilePath(),
            "dob"=> $this->dob,
            "village"=> $this->village,
            "city"=> $this->city,
            "province"=> $this->province

        ];
    }
}
