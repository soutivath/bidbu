<?php

namespace App\Http\Resources;

use Carbon\carbon;
use File;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
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
        $files = File::files(public_path('/buddhist_images/' . $this->buddhist->image_path . "/"));
        $file_path = pathinfo($files[0]);
        \array_push($allImage, Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
            "/" . "buddhist_images/" . $this->buddhist->image_path . "/" . $file_path['basename']);

        return [
            'id' => $this->id,
            'buddhist_id' => $this->buddhist->id,
            'user_id' => $this->user->id,
            'name' => $this->buddhist->name,
            'place' => $this->buddhist->place,
            'time_remain' => Carbon::now()->lessThan(Carbon::parse($this->end_time)) ? Carbon::now()->diffInSeconds(Carbon::parse($this->end_time)) : 0,
            'picture' => $allImage,

        ];
    }
}
