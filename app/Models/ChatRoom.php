<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;
    protected $table = "chat_room";
    protected $fillable = ["user_1", "user_2", "buddhist_id"];
    public function user1()
    {
        return $this->belongsTo(User::class, "user_1", "id");
    }
    public function user2()
    {
        return $this->belongsTo(User::class, "user_2", "id");
    }

 

}
