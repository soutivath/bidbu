<?php

namespace App\Models;

use App\Models\Buddhist;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendedBuddhist extends Model
{
    use HasFactory;

    protected $fillable = ['buddhist_id'];
    protected $table = "recommended_buddhists";
    public function buddhist()
    {
        return $this->belongsTo(Buddhist::class);

    }
}
