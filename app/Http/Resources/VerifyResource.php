<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;
class VerifyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $verify = null;
        $allImage = array();
        $files=null;
        if($this->file_folder_path!=null){
            $files = File::files(public_path('/verification_images/' . $this->file_folder_path . "/"));
            foreach ($files as $file) {
                $file_path = pathinfo($file);
                \array_push($allImage, $this->getImagePath() . "/" . $file_path['basename']);
            }
        }
       
      
       
        return [
            "id"=>$this->user->id,
            "name"=>$this->user->name,
            "surname"=>$this->user->surname,
            "gender"=>$this->user->gender,
            "date_of_birth"=>$this->user->date_of_birth,
            "address"=>$this->address,
            "phone_number"=>$this->phone_number,
            "emegency_phone_number"=>$this->emegency_phone_number,
            "email_address"=>$this->user->email_address,
            "verify"=> [
                "address"=>$this->address,
                "address_verify_status"=>$this->address_verify_status,
                "phone_number"=>$this->phone_number,
                "phone_number_verify_status"=>$this->phone_number_verify_status,
                "file_type"=>$this->file_type,
                "file_folder_path"=>$allImage,
                "file_verify_status"=>$this->file_verify_status,
            ]
            
        ];
    }
}
