<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BuddhistResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [ 
            'id'=>$this->id,
            'name'=>$this->name,
            'time_remain'=>$this->end_time->diffInSeconds($this->start_time),
            'picture'=>'picture_path',
            'position'=>'vientiane',
        ];
    }
}
