<?php

namespace App\Http\Resources;

use Carbon\carbon;
use File;
use Illuminate\Http\Resources\Json\JsonResource;
use Request;

class BuddhistResource extends JsonResource
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
        /* foreach($files as $file){
        $file_path = pathinfo($file);
        \array_push($allImage,"buddhist_images/".$this->image_path."/".$file_path['basename']);
        }*/
        $file_path = pathinfo($files[0]);
        \array_push($allImage, Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
            "/" . "buddhist_images/" . $this->image_path . "/" . $file_path['basename']);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'highest_price' => $this->highest_price,
            'place' => $this->place,
            'time_remain' => Carbon::now()->lessThan(Carbon::parse($this->end_time)) ? Carbon::now()->diffInSeconds(Carbon::parse($this->end_time)) : 0,
            'type' => [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ],
            'image' => $allImage,
        ];
    }
}
