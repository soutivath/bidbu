<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use File;
use Config;
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
        $files= File::files(public_path('/buddhist_images/'.$this->buddhist->image_path."/"));
        
        $file_path = pathinfo($files[0]);
        \array_push($anImage,Config("values.APP_URL").":".$_SERVER["SERVER_PORT"].
        "/"."buddhist_images/".$this->buddhist->image_path."/".$file_path['basename']);

        
        return [
            'buddhist_id'=>$this->buddhist_id,
            'image'=>$anImage,
            'buddhist_name'=>$this->buddhist->name,
            'price'=>$this->buddhist->price,
            'time'=>$this->created_at,
            'read'=>$this->read

        ];
    }
}
