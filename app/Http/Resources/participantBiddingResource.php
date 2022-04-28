<?php

namespace App\Http\Resources;

use App\Enums\VerifyStatus;
use carbon\Carbon;
use File;
use Illuminate\Http\Resources\Json\JsonResource;

class participantBiddingResource extends JsonResource
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'highest_price' => $this->highest_price,
            'time_remain' => Carbon::now()->lessThan(Carbon::parse($this->end_time)) ? Carbon::now()->diffInSeconds(Carbon::parse($this->end_time)) : 0,
            'image' => $allImage,
            'place' => $this->place,
            'is_verify'=>$this->file_verify_status==VerifyStatus::APPROVED?true:false
        ];
    }
}
