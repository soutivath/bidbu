<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
    protected $fillable = ['name','image_path'];
    public $table="types";
    public function buddhists()
    {
        return $this->hasMany(Buddhist::class);
    }

    public function getTypePath()
    {
        return Config("values.APP_URL").":".$_SERVER["SERVER_PORT"].
        "/"."type_images/".$this->image_path;
    }
}
