<?php

namespace App\Console\Commands;

use Google\Cloud\Storage\Notification;
use Illuminate\Console\Command;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as MessagingNotification;
use App\Models\Buddhist;
class sendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $messaging = app('firebase.messaging');
        $buddhists = Buddhist::where([["end_time","<=",now()],['active','1']])->get();
        foreach($buddhists as $buddhist)
        {
            
            $deviceToken = $buddhist->winner_fcm_token;
            if($deviceToken=="empty")
            {
                $notification = Notification::fromArray([
                    'title' => 'ທ່ານມີການແຈ້ງເຕືອນໃໝ່ຈາກ '.$buddhist->id.' ທີ່ທ່ານໄດ້ປ່ອຍ',
                    'body' => 'ການປະມູນຈົບລົງແລ້ວ ບໍ່ມີຄົນເຂົ້າຮ່ວມການປະມູນຂອງທ່ານ',
                    'image' => \public_path("/notification_images/chat.png"),
                ]);
                $notification_data = [
                    'buddhist_id' => $buddhist->id,
                    'page'=>'content_detail',
                ];
                $message = CloudMessage::withTarget('topic', "B".$id)
                    ->withNotification($notification)
                    ->withData($notification_data);
                $messaging->send($message);
            }else{
                $notification = Notification::fromArray([
                    'title' => 'ທ່ານມີການແຈ້ງເຕືອນໃໝ່ຈາກ '.$buddhist->id.' ທີ່ທ່ານໄດ້ປ່ອຍ',
                    'body' => 'ການປະມູນຈົບລົງແລ້ວ '.$buddhist->user->name.' ຊະນະການປະມູນດ້ວຍເງິນຈຳນວນ '.$buddhist->highest_price." ກີບ",
                    'image' => \public_path("/notification_images/chat.png"),
                ]);
                $notification_data = [
                    'buddhist_id' => $buddhist->id,
                    'page'=>'content_detail',
                ];
                $message = CloudMessage::withTarget('topic', "B".$id)
                    ->withNotification($notification)
                    ->withData($notification_data);
                $messaging->send($message);
//***** Send to winner */
                $notification = Notification::fromArray([
                    'title' => 'ທ່ານມີການແຈ້ງເຕືອນໃໝ່ຈາກ '.$buddhist->id.' ທີ່ທ່ານໄດ້ປະມູນ',
                    'body' => 'ການປະມູນຈົບລົງແລ້ວ ທ່ານຊະນະການປະມູນດ້ວຍເງິນຈຳນວນ '.$buddhist->highest_price." ກີບ",
                    'image' => \public_path("/notification_images/chat.png"),
                ]);
                $notification_data = [
                    'buddhist_id' => $buddhist->id,
                    'page'=>'content_detail',
                ];
                $message = CloudMessage::withTarget('token', $buddhist->winner_fcm_token)
                    ->withNotification($notification)
                    ->withData($notification_data);
                $messaging->send($message);
            }
            $buddhist->active = "0";
            $buddhist->save();
        }

        


        echo "Operation done";
    }
}
