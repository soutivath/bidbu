<?php

namespace App\Models;

use App\Models\App;
use App\Models\ChatRoom;
use App\Models\RecommendedBuddhist;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buddhist extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'image_path'];
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
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function notifications()
    {
        return $this->hasMany(NotificationFirebase::class);
    }

    public function recommended()
    {
        return $this->hasOne(RecommendedBuddhist::class);
    }

    public function chat_room_user1()
    {
        return $this->hasMany(ChatRoom::class, "user_1");
    }
    public function chat_room_user2()
    {
        return $this->hasMany(ChatRoom::class, "user_2");
    }

}
