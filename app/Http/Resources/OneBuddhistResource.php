<?php

namespace App\Http\Resources;

use App\Models\favourite;
use Auth;
use Carbon\carbon;
use File;
use Illuminate\Http\Resources\Json\JsonResource;

class OneBuddhistResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public function toArray($request)
    {

        $boolLike = 2;

        if (Auth::guard('api')->check()) {
            $favorite = favourite::where([
                ['user_id', '=', Auth::guard('api')->id()],
                ['buddhist_id', '=', $this->id],
            ])->get();

            if ($favorite->isEmpty()) {
                $boolLike = 0;
            } else {
                $boolLike = 1;
            }
        } else {
            $boolLike = 2;
        }

        $allImage = array();
        $files = File::files(public_path('/buddhist_images/' . $this->image_path . "/"));
        foreach ($files as $file) {
            $file_path = pathinfo($file);
            \array_push($allImage, Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
                "/" . "buddhist_images/" . $this->image_path . "/" . $file_path['basename']);
        }
        return [
            ['id' => $this->id,
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
                'favorite' => $boolLike, // login but unlike 0, login but like 1 , unAuth2
                'image' => $allImage,
                //  'pay_choice' => $this->pay_choice,
                // 'bank_name' => $this->bank_name,
                //  'account_name' => $this->account_name,
                // 'account_number' => $this->account_number,
                //  'sending_choice' => $this->sending_choice,
                //  'place_send' => $this->place_send,
                //  'tel' => $this->tel,
                // 'more_info' => $this->more_info,
                'place' => $this->place,
                'status' => $this->status,
                'highBidUser' => $this->highBidUser,
                'favoriteCount' => favourite::where("buddhist_id", $this->id)->count(),
                'priceSmallest' => $this->priceSmallest,
                'minimum_price'=>$this->minimum_price,
            ],
        ];
    }
}
