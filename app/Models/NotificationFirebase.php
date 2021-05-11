<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\App;

class NotificationFirebase extends Model
{
    use HasFactory;
    protected $fillable = ['notification_time','buddhist_id','user_id','read','biddingPrice'];
    protected $table = "notification";
    public function buddhist()
    {
        return $this->belongsTo(Buddhist::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    

}
