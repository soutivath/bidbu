<?php

namespace App\Http\Resources;

use App\Models\ChatRoom;
use Config;
use File;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $anImage = array();
        $sender_name="";
        $sender_id="";
        if($this->notification_type=="result_message")
        {
            $chat_data = ChatRoom::where("buddhist_id",$this->buddhist_id)->first();
            if(!empty($chat_data)){
                if(Auth::id()==$chat_data->user_1)
                {
                    $sender_name = $chat_data->user2->name;
                    $sender_id = $chat_data->user2->id;
                    \array_push($anImage,$chat_data->user2->getProfilePath());

                }
                else{
                    $sender_name = $chat_data->user1->name;
                    $sender_id = $chat_data->user1->id;
                    \array_push($anImage,$chat_data->user1->getProfilePath());

                }
            }

        }
        else{
            $files = File::files(public_path('/buddhist_images/' . $this->buddhist->image_path . "/"));
            $file_path = pathinfo($files[0]);
            \array_push($anImage, Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
                "/" . "buddhist_images/" . $this->buddhist->image_path . "/" . $file_path['basename']);
        }


        return [
            'buddhist_id' => $this->buddhist_id,
            'image' => $anImage,
            'buddhist_name' => $this->buddhist->name,
            'data' => $this->data,
            'time' => $this->notification_time,
            'read' => $this->read,
            'notification_type' => $this->notification_type,
            'comment_path' => $this->comment_path,
            'type' => $this->type_id,
            "sender_name"=>$sender_name,
            "sender_id"=>$sender_id
        ];
    }
}
