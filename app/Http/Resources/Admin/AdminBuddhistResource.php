<?php

namespace App\Http\Resources\Admin;

use App\Models\User;
use Carbon\carbon;
use File;
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
            if ($highBidUser->id == $this->user_id) {
                $highBidUser = null;
            }
        } catch (ModelNotFoundException $e) {
            $highBidUser = null;
        }
        $active;
        if ($this->active == "disabled") {
            $active = "disabled";
        } else {
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

            'time_remain' => Carbon::now()->lessThan(Carbon::parse($this->end_time)) ? Carbon::now()->diffInSeconds(Carbon::parse($this->end_time)) : 0,
            'type' => [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ],

            'user' => $this->user->name,
            'owner_id' => $this->user_id,

            'images' => $allImage,

            'tel' => $this->user->phone_number,

            'place' => $this->place,
            'status' => $this->status,
            'highBidUserName' => $highBidUser != null ? $highBidUser->name : "NOT FOUND",
            'highBidUserID' => $highBidUser != null ? $highBidUser->id : "null",
            'active' => $active,
            'priceSmallest' => $this->priceSmallest,

        ];

    }
}
