<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminLoginResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "token_type" => $this->token_type,
            "Bearer" => $this->Bearer,
            "expires_in" => $this->expires_in,
            "access_token" => $this->access_token,
            "refresh_token" => $this->refresh_token,
            "role" => $this->roles[0]->name,
            "username" => $this->name,
        ];
    }
}
