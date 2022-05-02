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
        $allImageName = array();
        $files=null;
        if($this->file_folder_path!=null){
            $files = File::files(base_path("resources/private/verify/" . $this->file_folder_path . "/"));
            foreach ($files as $file) {
                $file_path = pathinfo($file);
                \array_push($allImageName, $file_path['basename']);
            }
        }
       
      
       
        return [
            "id"=>$this->user->id,
            "name"=>$this->user->name,
            "surname"=>$this->user->surname,
            "gender"=>$this->user->gender,
            "date_of_birth"=>$this->user->date_of_birth,
            "address"=>$this->address,
            "user_phone_number"=>$this->user->phone_number==null?$this->user->email_address:$this->user->phone_number,
            "phone_number"=>$this->phone_number,
            "emegency_phone_number"=>$this->emegency_phone_number,
            "email_address"=>$this->user->email_address,
            "profilePicture"=>$this->user->getProfilePath(),
            "verify"=> [
                "verify_id"=>$this->id,
                "address"=>$this->address,
                "address_verify_status"=>$this->address_verify_status,
                "phone_number"=>$this->phone_number,
                "phone_verify_status"=>$this->phone_verify_status,
                "file_type"=>$this->file_type,
                "files_name"=>$allImageName,
                "folderName"=>$this->file_folder_path,
                "file_verify_status"=>$this->file_verify_status,
                "verify_name"=>$this->verify_name,
                "verify_surname"=>$this->verify_surname,
                "verify_phone_number"=>$this->verify_phone_number,
                "verify_gender"=>$this->verify_gender,
                "verify_date_of_birth"=>$this->verify_date_of_birth,
            ]
            
        ];
    }
}
