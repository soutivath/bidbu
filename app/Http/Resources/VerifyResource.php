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
        $verify = [];
        $allImage = array();
        $files = File::files(public_path('/verification_images/' . $this->images_folder . "/"));
        foreach ($files as $file) {
            $file_path = pathinfo($file);
            \array_push($allImage, $this->getImagePath() . "/" . $file_path['basename']);
        }
        if($this->verify()->exists()){
            $verify = [
                "address"=>$this->verify->address,
                "address_verify_status"=>$this->verify->address_verify_status,
                "phone_number"=>$this->verify->phone_number,
                "phone_number_verify_status"=>$this->verify->phone_number_verify_status,
                "file_type"=>$this->verify->file_type,
                "file_folder_path"=>$allImage,
                "file_verify_status"=>$this->verify->file_verify_status,
            ];
        }
       
        return [
            "id"=>$this->id,
            "name"=>$this->name,
            "surname"=>$this->surname,
            "gender"=>$this->gender,
            "date_of_birth"=>$this->date_of_birth,
            "address"=>$this->verify->address,
            "phone_number"=>$this->verify->phone_number,
            "emegency_phone_number"=>$this->verify->emegency_phone_number,
            "verify"=>$verify
            
        ];
    }
}
