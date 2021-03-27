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
        $message = CloudMessage::withTarget('token',$deviceToken)
        ->withNotification(MessagingNotification::create('title','body'))
        ->withData(['buddhist_id'=>'value']);
        $messaging->send($message);

        $buddhists = Buddhist::where(["end_time","<=",now()],['active','0'])->get();
        foreach($buddhists as $buddhist)
        {
            $buddhist->active = "0";
            $buddhist->save();
        }

        


        echo "Operation done";
    }
}
