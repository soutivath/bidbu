<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buddhist extends Model
{
    use HasFactory;
    protected $fillable = ['name','image_path'];
    public function type()
    {
        return $this->belongsTo(Type::class);
    }
    public function favorite()
    {
        $this->hasMany("App\favourite");
    }
    public function user()
    {
        $this->belongsTo("App\User");

    }
    public function comments()
    {
        $this->hasMany("App\comment");
    }
    

}
