<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewDetail extends Model
{
    use HasFactory;
    protected $table="review_details";
    protected $fillable = ["score","comment","user_id","buddhist_id","review_id"];
    public function review()
    {
        return $this->belongsTo(Review::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);

    }
    public function buddhist()
    {
        return $this->belongsTo(Buddhist::class);
    }

}
