<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BannerTranController extends Controller
{
    //

    protected $fillable = ["image_path","banner_id"];
    public function banner()
    {
        return $this->belongTo(Banner::class);
    }

    public function language()
    {
        return $this->belongTo(Language::class);
    }
}
