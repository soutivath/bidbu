<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerTran extends Model
{
    use HasFactory;
    protected $fillable = ["image_path","language_id","banner_id"];
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }
    public function getImagePath()
    {
        return Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
        "/" . "banner_images/" . $this->image_path;
    }
}
