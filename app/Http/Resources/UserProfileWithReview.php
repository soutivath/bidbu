<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileWithReview extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id"=>$this->id,
            "all_star"=>$this->review_details->sum("score"),
            "review"=>$this->review_details
        
            // "review_details"=>[
            // "id"=>$this->review_details->id,
            // "score"=>$this->review_details->score,
            // "comment"=>$this->review_details->comment,
            // "review_id"=>$this->review_details->review_id,
            // "user_id"=>$this->review_details->user_id,
            // "created_at"=>$this->review_details->created_at,
            // "user"=>[
            //    "id"=> $this->review_details[0]["user"]["id"],
            //    "name"=> $this->review_details[0]["user"]["name"],
            //    "surname"=> $this->review_details[0]["user"]["surname"],
            //    "phone_number"=> $this->review_details[0]["user"]["phone_number"],
            //    "created_at"=> $this->review_details[0]["user"]["created_at"],
             
            // ],
        
            
        ];

    }
}

