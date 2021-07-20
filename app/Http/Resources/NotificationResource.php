<?php

namespace App\Http\Resources;

use Config;
use File;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $anImage = array();
        $files = File::files(public_path('/buddhist_images/' . $this->buddhist->image_path . "/"));

        $file_path = pathinfo($files[0]);
        \array_push($anImage, Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
            "/" . "buddhist_images/" . $this->buddhist->image_path . "/" . $file_path['basename']);

        return [
            'buddhist_id' => $this->buddhist_id,
            'image' => $anImage,
            'buddhist_name' => $this->buddhist->name,
            'data' => $this->buddhist->price,
            'time' => $this->created_at,
            'read' => $this->read,
            'notification_type' => $this->notification_type,
            'comment_path' => $this->comment_path,

        ];
    }
}
