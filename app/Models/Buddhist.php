<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\App;

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
       return $this->hasMany(favourite::class);
    }
    public function user()
    {
       return $this->belongsTo(User::class);

    }
    public function comments()
    {
       return $this->hasMany(comment::class);
    }
    

}
