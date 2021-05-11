<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use File;
use Carbon\carbon;
class buddhistCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
       
        $allImage = array();
        $files= File::files(public_path('/buddhist_images/'.$this->image_path."/"));
        $file_path = pathinfo($files[0]);
        \array_push($allImage,Config("values.APP_URL").":".$_SERVER["SERVER_PORT"].
        "/"."buddhist_images/".$this->image_path."/".$file_path['basename']);
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'place'=>$this->place,
            'picture'=>$allImage,
            'time_remain'=>Carbon::now()->lessThan(Carbon::parse($this->end_time))?Carbon::now()->diffInSeconds(Carbon::parse($this->end_time)):"0",
        ];
    }
}
