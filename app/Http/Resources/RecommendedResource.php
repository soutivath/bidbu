<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecommendedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /* $allImage = array();
        $files = File::files(public_path('/buddhist_images/' . $this->image_path . "/"));
        $file_path = pathinfo($files[0]);
        \array_push($allImage, Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
        "/" . "buddhist_images/" . $this->image_path . "/" . $file_path['basename']);*/

        //  return [
        /* "id" => $this->id,
        "name" => $this->name,
        "detail" => $this->detail,
        "end_time" => $this->end_time,
        "highest_price" => $this->highest_price,
        "image" => $allImage,
        "recommended" => $this->recommended == null ? "0" : "1",*/

        //  ];
        return parent::toArray($request);
    }
}
