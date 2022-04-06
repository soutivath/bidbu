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
        $verify =null;
        if($this->verify()->exists()){
            $verify =[
                "address"=>$this->verify[0]->address,
                "address_verify_status"=> $this->verify[0]->address_verify_status,
                "phone_number"=> $this->verify[0]->phone_number,
                "phone_verify_status"=> $this->verify[0]->phone_verify_status,
                "file_type"=>$this->verify[0]->file_type,
                "file_verify_status"=> $this->verify[0]->file_verify_status,
                "created_at"=>$this->verify[0]->created_at,
                "updated_at"=> $this->verify[0]->updated_at
            ];
        }
        else{
            $verify =[
                "address"=>null,
                "address_verify_status"=>null,
                "phone_number"=>null,
                "phone_verify_status"=>null,
                "file_type"=>null,
                "file_verify_status"=> null,
                "created_at"=>null,
                "updated_at"=> null
            ];
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'phone_number' => $this->phone_number,
            'picture' => $this->getProfilePath(),
            "gender"=> $this->gender,
            "date_of_birth"=> $this->date_of_birth,
            "emergency_phone_number"=> $this->emergency_phone_number,
            "email_address"=> $this->email_address,
           
            'verify'=>$verify,
            'created_at'=>$this->created_at

        ];
    }
}
