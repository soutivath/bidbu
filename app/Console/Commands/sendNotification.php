<?php

namespace App\Console\Commands;

use App\Models\Buddhist;
use App\Models\NotificationFirebase;
use App\Models\User;
use Illuminate\Console\Command;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

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
        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',

        ]);

        $buddhists = Buddhist::where([["end_time", "<=", now()], ['active', '1']])->get();
        foreach ($buddhists as $buddhist) {

            $deviceToken = $buddhist->winner_fcm_token;
            $winner_uid = $buddhist->winner_user_id;

            if ($deviceToken == "empty") {
                /* $notification = Notification::fromArray([
                'title' => 'ທ່ານມີການແຈ້ງເຕືອນໃໝ່ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                'body' => 'ການປະມູນຈົບລົງແລ້ວ ບໍ່ມີຄົນເຂົ້າຮ່ວມການປະມູນຂອງທ່ານ',
                'image' => \public_path("/notification_images/chat.png"),
                ]);
                $notification_data = [
                'buddhist_id' => $buddhist->id,
                'page' => 'content_detail',
                ];
                $message = CloudMessage::withTarget("topic", $buddhist->user->topic)
                ->withNotification($notification)
                ->withData($notification_data);*/
                NotificationFirebase::create([
                    'notification_time' => date('Y-m-d H:i:s'),
                    'read' => 0,
                    'data' => "no_participant",
                    'buddhist_id' => $buddhist->id,
                    'user_id' => $buddhist->user->id,
                    'notification_type' => "owner_result",
                    'comment_path' => 'empty',
                ]);

                $message = CloudMessage::withTarget("topic", $buddhist->user->topic)
                    ->withNotification(Notification::fromArray([
                        'title' => 'ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                        'body' => 'ການປະມູນຈົບລົງແລ້ວ ບໍ່ມີຄົນເຂົ້າຮ່ວມການປະມູນຂອງທ່ານ',
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'buddhist_id' => $buddhist->id,
                        'type' => '',
                        'sender' => "0",
                        'result' => "no_participant",
                    ]);
                $message = $message->withAndroidConfig($androidConfig);
                $messaging->send($message);
            } else {
                $userData = User::where("firebase_uid", $buddhist->winner_user_id)->first();


                /*$notification = Notification::fromArray([
                'title' => 'ທ່ານມີການແຈ້ງເຕືອນໃໝ່ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                'body' => 'ການປະມູນຈົບລົງດ້ວຍເງິນຈຳນວນ ' . $buddhist->highest_price . " ກີບ",
                'image' => \public_path("/notification_images/chat.png"),
                ]);
                $notification_data = [
                'buddhist_id' => $buddhist->id,
                'page' => 'content_detail',
                ];
                $message = CloudMessage::withTarget('topic', $buddhist->user->topic)
                ->withNotification($notification)
                ->withData($notification_data);*/
                /*  






                */
                // $message = CloudMessage::withTarget('topic', $buddhist->user->topic)
                //     ->withNotification(Notification::fromArray([
                //         'title' => 'ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                //         'body' => 'ການປະມູນຈົບລົງດ້ວຍເງິນຈຳນວນ ' .  number_format($buddhist->highest_price,2,".",",") . " ກີບ",
                //         'image' => \public_path("/notification_images/chat.png"),
                //     ]))
                //     ->withData([
                //         'buddhist_id' => $buddhist->id,
                //         'type' => '',
                //         'sender' => "0",
                //         'result' => "have_participant",
                //     ]);
                // $message = $message->withAndroidConfig($androidConfig);
                // $messaging->send($message);

/**
 * 
 * 
 * 
 * 
 */


//***** Send to winner */
                /* $notification = Notification::fromArray([
                'title' => 'ທ່ານມີການແຈ້ງເຕືອນໃໝ່ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປະມູນ',
                'body' => 'ການປະມູນຈົບລົງແລ້ວ ທ່ານຊະນະການປະມູນດ້ວຍເງິນຈຳນວນ ' . $buddhist->highest_price . " ກີບ",
                'image' => \public_path("/notification_images/chat.png"),
                ]);
                $notification_data = [
                'buddhist_id' => $buddhist->id,
                'page' => 'content_detail',
                ];
                $message = CloudMessage::withTarget('topic', $userData->topic)
                ->withNotification($notification)
                ->withData($notification_data);*/
                $notificationData = NotificationFirebase::
                    where([
                    ["buddhist_id", $buddhist->id],
                ])
                    ->where("notification_type", "bidding_participant")
                    ->select("user_id")->distinct()->get();
                  
                
                if($buddhist->highest_price<$buddhist->minimum_price)
                {
                    $messageWinner = CloudMessage::withTarget('topic', $userData->topic)
                    ->withNotification(Notification::fromArray([
                        'title' => 'ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປະມູນ',
                        'body' => 'ທ່ານປະມູນສູງສຸດດ້ວຍເງິນຈຳນວນ ' .number_format($buddhist->highest_price,2,".",",") . " ກີບ ແຕ່ຈຳນວນເງິນຂັ້ນຕ່ຳບໍ່ພຽງພໍ",
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'buddhist_id' => $buddhist->id,
                        'type' => 'bidding_result',
                        'sender' => "0",
                        'result' => "not_reach_the_minimum",
                    ]);
                $messageWinner = $messageWinner->withAndroidConfig($androidConfig);
                $messaging->send($messageWinner);
                for ($i = 0; $i < count($notificationData); $i++) {
                    NotificationFirebase::create([
                        'notification_time' => date('Y-m-d H:i:s'),
                        'read' => 0,
                        'data' => "not_reach_the_minimum", //winner id
                        'buddhist_id' => $buddhist->id,
                        'user_id' => $notificationData[$i]["user_id"],
                        'notification_type' => "bidding_result",
                        'comment_path' => 'empty',
                    ]);

                }
                NotificationFirebase::create([
                    'notification_time' => date('Y-m-d H:i:s'),
                    'read' => 0,
                    'data' => "have_participant_not_meet_minimum", //winner id
                    'buddhist_id' => $buddhist->id,
                    'user_id' => $buddhist->user->id,
                    'notification_type' => "owner_result",
                    'comment_path' => 'empty',
                ]);
                 $message = CloudMessage::withTarget('topic', $buddhist->user->topic)
                    ->withNotification(Notification::fromArray([
                        'title' => 'ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                        'body' => 'ການປະມູນຈົບລົງດ້ວຍເງິນຈຳນວນ ' .  number_format($buddhist->highest_price,2,".",",") . " ກີບ",
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'buddhist_id' => $buddhist->id,
                        'type' => '',
                        'sender' => "0",
                        'result' => "have_participant",
                    ]);
                $message = $message->withAndroidConfig($androidConfig);
                $messaging->send($message);


                /********* */
                $message1 = CloudMessage::withTarget('topic', $buddhist->user->topic)
                ->withNotification(Notification::fromArray([
                    'title' => 'ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                    'body' => 'ການປະມູນຈົບລົງແຕ່ບໍ່ຮອດຂັ້ນຕ່ຳດ້ວຍເງິນຈຳນວນ ' .  number_format($buddhist->highest_price,2,".",",") . " ກີບ",
                    'image' => \public_path("/notification_images/chat.png"),
                ]))
                ->withData([
                    'buddhist_id' => $buddhist->id,
                    'type' => '',
                    'sender' => "0",
                    'result' => "have_participant_not_meet_minimum",
                ]);
            $message1 = $message1->withAndroidConfig($androidConfig);
            $messaging->send($message1);



                /******** */

                $buddhist->winner_user_id="empty";
                }
                else{

                    $messageWinner = CloudMessage::withTarget('topic', $userData->topic)
                    ->withNotification(Notification::fromArray([
                        'title' => 'ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປະມູນ',
                        'body' => 'ທ່ານຊະນະການປະມູນດ້ວຍເງິນຈຳນວນ ' .number_format($buddhist->highest_price,2,".",",") . " ກີບ",
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'buddhist_id' => $buddhist->id,
                        'type' => 'bidding_result',
                        'sender' => "0",
                        'result' => "win",
                    ]);
                $messageWinner = $messageWinner->withAndroidConfig($androidConfig);
                $messaging->send($messageWinner);
                for ($i = 0; $i < count($notificationData); $i++) {
                    NotificationFirebase::create([
                        'notification_time' => date('Y-m-d H:i:s'),
                        'read' => 0,
                        'data' => $userData->id, //winner id
                        'buddhist_id' => $buddhist->id,
                        'user_id' => $notificationData[$i]["user_id"],
                        'notification_type' => "bidding_result",
                        'comment_path' => 'empty',
                    ]);

                }
                NotificationFirebase::create([
                    'notification_time' => date('Y-m-d H:i:s'),
                    'read' => 0,
                    'data' => "have_participant", //winner id
                    'buddhist_id' => $buddhist->id,
                    'user_id' => $buddhist->user->id,
                    'notification_type' => "owner_result",
                    'comment_path' => 'empty',
                ]);

                $message1 = CloudMessage::withTarget('topic', $buddhist->user->topic)
                    ->withNotification(Notification::fromArray([
                        'title' => 'ຈາກ ' . $buddhist->name . ' ທີ່ທ່ານໄດ້ປ່ອຍ',
                        'body' => 'ການປະມູນຈົບລົງດ້ວຍເງິນຈຳນວນ ' .  number_format($buddhist->highest_price,2,".",",") . " ກີບ",
                        'image' => \public_path("/notification_images/chat.png"),
                    ]))
                    ->withData([
                        'buddhist_id' => $buddhist->id,
                        'type' => '',
                        'sender' => "0",
                        'result' => "have_participant",
                    ]);
                $message1 = $message1->withAndroidConfig($androidConfig);
                $messaging->send($message1);

                
                }







                /*  $bidding_notification = Notification::fromArray([
                'title' => 'ຈາກ ' . $buddhist->name,
                'body' => 'ການປະມູນຈົບລົງແລ້ວ ທ່ານປະມູນບໍ່ຊະນະ',
                'image' => \public_path("/notification_images/chat.png"),

                ]);
                $bidding_notification_data = [
                'sender' => $userData->id,
                'buddhist_id' => $buddhist->id,
                'page' => 'homepage',

                ];
                $bidding_message = CloudMessage::withTarget('topic', $buddhist->topic)
                ->withNotification($bidding_notification)
                ->withData($bidding_notification_data);
                $messaging->send($bidding_message);*/
                //  $messaging->unsubscribeFromTopic($buddhist->topic, $userData->winner_fcm_token);

                $loseCondition = "'" . $buddhist->topic . "' in topics && !('" . $userData->topic . "' in topics)";

                $bidding_message = CloudMessage::withTarget('condition', $loseCondition)
                    ->withNotification(Notification::fromArray([
                        'title' => 'ຈາກ ' . $buddhist->name,
                        'body' => 'ການປະມູນຈົບລົງແລ້ວ ທ່ານປະມູນບໍ່ຊະນະ',
                        'image' => \public_path("/notification_images/chat.png"),

                    ]))
                    ->withData([
                        'buddhist_id' => $buddhist->id,
                        'type' => 'bidding_result',
                        'sender' => $userData->id,
                        'result' => "lose",
                    ]);
                $bidding_message = $bidding_message->withAndroidConfig($androidConfig);
                $messaging->send($bidding_message);
            }
            $buddhist->active = "0";
            $buddhist->save();
        }


        echo "Operation done";
    }
}
