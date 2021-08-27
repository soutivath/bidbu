<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RecommendedResource extends ResourceCollection
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
            'current_page' => $this->currentPage(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
            "data" => ["id" => $this->id,
                "name" => $this->name,
                "detail" => $this->detail,
                "end_time" => $this->end_time,
                "highest_price" => $this->highest_price,
                "image" => $allImage,
                "recommended" => $this->recommended == null ? "0" : "1"],

        ];

    }
}
