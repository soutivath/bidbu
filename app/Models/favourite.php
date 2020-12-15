<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class favourite extends Model
{
    use HasFactory;
   
    public function user()
    {
        $this->belongsTo("app\user");
    }
    public function buddhist()
    {
        $this->belongsTo("app\Buddhist");
    }
}
