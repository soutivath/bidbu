<?php

namespace App\Http\Resources\Admin;

use App\Models\User;
use carbon\Carbon;
use File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBuddhistResource extends JsonResource
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
        foreach ($files as $file) {
            $file_path = pathinfo($file);
            \array_push($allImage, Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
                "/" . "buddhist_images/" . $this->image_path . "/" . $file_path['basename']);
        }
        $highBidUser;
        try {
            $highBidUser = User::find($this->highBidUser);
        } catch (ModelNotFoundException $e) {
            $highBidUser = null;
        }
        $active;
        if($this->active==0)
        {
            $active = "disable";
        }
        else{
            $active = Carbon::now()->lessThan(Carbon::parse($this->end_time)) ? 1 : 0;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'detail' => $this->detail,
            'price' => $this->price,
            'highest_price' => $this->highest_price,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,

            'time_remain' => Carbon::now()->lessThan(Carbon::parse($this->end_time)) ? Carbon::now()->diffInSeconds(Carbon::parse($this->end_time)) : "Item is expired",
            'type' => [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ],

            'user' => $this->user->name,
            'owner_id' => $this->user_id,

            'images' => $allImage,
            'pay_choice' => $this->pay_choice,
            'bank_name' => $this->bank_name,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'sending_choice' => $this->sending_choice,
            'place_send' => $this->place_send,
            'tel' => $this->tel,
            'more_info' => $this->more_info,
            'place' => $this->place,
            'status' => $this->status,
            'highBidUserName' => $highBidUser != null ? $highBidUser->name : "NOT FOUND",
            'highBidUserID' => $highBidUser != null ? $highBidUser->id : "null",
            'active' => $active,
            'priceSmallest' => $this->priceSmallest,

        ];

    }
}
