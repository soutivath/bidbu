<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class favourite extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function buddhist()
    {
        return $this->belongsTo(Buddhist::class);
    }
    protected $fillable = ['user_id','buddhist_id'];
}
