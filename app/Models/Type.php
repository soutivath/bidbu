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
}
