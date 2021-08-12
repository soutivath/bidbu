<?php

namespace App\Http\Resources;

use App\Models\User;
use Auth;
use File;
use Illuminate\Http\Resources\Json\JsonResource;

class checkBuddhistResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $allImage = array();
        $files = File::files(public_path('/buddhist_images/' . $this->image_path . "/"));
        $file_path = pathinfo($files[0]);
        \array_push($allImage, Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
            "/" . "buddhist_images/" . $this->image_path . "/" . $file_path['basename']);
        $winner_name = User::where("firebase_uid", $this->winner_user_id)->first();
        return [
            "text" => $this->winner_user_id == Auth::user()->firebase_uid ? "ທ່ານຊະນະການປະມູນໃນຄັ້ງນີ້" : "ທ່ານແພ້ການປະມູນໃນຄັ້ງນີ້",
            "winner_name" => empty($winner_name) ? "ບໍ່ມີຜູ້ຊະນະ" : $winner_name->name,
            "winner_surname" => empty($winner_name) ? "ບໍ່ມີຜູ້ຊະນະ" : $winner_name->surname,
            "winner_price" => $this->highest_price,
            "owner_name" => $this->user->name,
            "owner_name" => $this->user->surname,
            "owner_phone" => $this->user->phone_number,
            "buddhist_image" => $allImage,
            "owner_image" => $this->user->getProfilePath(),
        ];
    }
}
