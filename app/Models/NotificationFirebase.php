<?php

namespace App\Models;

use App\Models\App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationFirebase extends Model
{
    use HasFactory;
    protected $fillable = ['notification_time', 'buddhist_id', 'user_id', 'read', 'data'];
    protected $table = "notification";
    public function buddhist()
    {
        return $this->belongsTo(Buddhist::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
