<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comment extends Model
{
    use HasFactory;

    protected $fillable = ['message'];
    public function buddhist()
    {
        $this->belongsTo("app\Buddhist");
    }
    public function user()
    {
        $this->belongsTo("app\User");
    }
    public function replies()
    {
        $this->hasMany("app\reply");
    }
}
