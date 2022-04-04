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
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'phone_number' => $this->phone_number,
            'picture' => $this->getProfilePath(),
            'verify'=>[
                "address"=>$this->verify[0]->address,
                "address_verify_status"=> $this->verify[0]->address_verify_status,
                "phone_number"=> $this->verify[0]->phone_number,
                "phone_verify_status"=> $this->verify[0]->phone_verify_status,
                "file_type"=>$this->verify[0]->file_type,
                "file_verify_status"=> $this->verify[0]->file_verify_status,
                "created_at"=>$this->verify[0]->created_at,
                "updated_at"=> $this->verify[0]->updated_at
            ]

        ];
    }
}
