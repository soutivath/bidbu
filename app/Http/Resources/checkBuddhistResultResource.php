<?php

namespace App\Http\Resources;

use App\Models\User;
use Auth;
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

        $winner_name = User::where("firebase_uid", $this->winner_user_id)->first();

        return [
            "text" => $this->winner_user_id == Auth::user()->firebase_uid ? "ທ່ານຊະນະການປະມູນໃນຄັ້ງນີ້" : "ທ່ານແພ້ການປະມູນໃນຄັ້ງນີ້",
            "winner_name" => empty($winner_name) ? "ບໍ່ມີຜູ້ຊະນະ" : $winner_name->name,
            "winner_price" => $this->highest_price,
            "owner_name" => $this->user->name,
            "owner_phone" => $this->user->phone_number,
        ];
    }
}
