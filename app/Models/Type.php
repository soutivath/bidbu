<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public $table="types";
    public function buddhists()
    {
        return $this->hasMany('App\Buddhist');
    }
}
