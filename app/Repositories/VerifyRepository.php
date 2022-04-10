<?php

namespace App\Repositories;

use App\Interfaces\VerifyInterface;
use App\Http\Requests\Verification\VerificationRequest;
use App\Traits\ResponseAPI;
use App\Traits\VerifyStatusHelper;
use App\Models\Verify;
use Illuminate\Support\Facades\DB;
use Image;
use App\Enums\VerifyStatus;
use Illuminate\Http\Request;
use App\Http\Resources\VerifyResource;
use App\Models\User;
use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Support\Facades\Auth;
use App\Enums\GenderEnum;
use App\Enums\VerifyFileType;
use App\Models\NotificationFirebase;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;

class VerifyRepository implements VerifyInterface
{
    use ResponseAPI, VerifyStatusHelper;
    public function getAllVerification(Request $request)
    {
        $verifiesData = Verify::with("user");
     
       
        if ($request->has("address_verify_status") && $request->address_verify_status ==VerifyStatus::APPROVED) {
           $verifiesData->orwhere("address_verify_status", VerifyStatus::APPROVED);
          
         
            //$address_verify_status = VerifyStatus::APPROVED;
        } else if ($request->has("address_verify_status")&&$request->address_verify_status  == VerifyStatus::PENDING) {
           // $address_verify_status = VerifyStatus::PENDING;
          $verifiesData->orwhere("address_verify_status",  VerifyStatus::PENDING);
        
        } else if ($request->has("address_verify_status")&&$request->address_verify_status == VerifyStatus::REJECTED) {
            //$address_verify_status = VerifyStatus::REJECTED;
           $verifiesData->orwhere("address_verify_status", VerifyStatus::REJECTED);
          
        }

        if ($request->has("phone_verify_status") &&$request->phone_verify_status== VerifyStatus::APPROVED) {
          $verifiesData->orwhere("phone_verify_status", VerifyStatus::APPROVED);
         
           // $phone_verify_status = VerifyStatus::APPROVED;
        } else if ($request->has("phone_verify_status") &&$request->phone_verify_status == VerifyStatus::PENDING) {
           // $phone_verify_status = VerifyStatus::PENDING;
         $verifiesData->orwhere("phone_verify_status", VerifyStatus::PENDING);
       
        } else if ($request->has("phone_verify_status")  &&$request->phone_verify_status== VerifyStatus::REJECTED) {
           // $phone_verify_status = VerifyStatus::REJECTED;
          $verifiesData->orwhere("phone_verify_status", VerifyStatus::REJECTED);
        
        }
        if ($request->has("file_verify_status")  &&$request->file_verify_status== VerifyStatus::APPROVED) {
            //$file_verify_status = VerifyStatus::APPROVED;
          $verifiesData->orwhere("file_verify_status", VerifyStatus::APPROVED);
         
        } else if ($request->has("file_verify_status")  &&$request->file_verify_status== VerifyStatus::PENDING) {
           // $file_verify_status = VerifyStatus::PENDING;
          $verifiesData->orwhere("file_verify_status", VerifyStatus::PENDING);
        
        } else if ($request->has("file_verify_status")  &&$request->file_verify_status== VerifyStatus::REJECTED) {
           // $file_verify_status = VerifyStatus::REJECTED;
           $verifiesData->orwhere("file_verify_status", VerifyStatus::REJECTED);
           
        }
        // ->orwhere("address_verify_status", $address_verify_status)
        // ->orWhere("phone_verify_status",$phone_verify_status)
        // ->orWhere("file_verify_status",$file_verify_status)->get();
        


        
        return VerifyResource::collection($verifiesData->get());
        //return $this->success("get data successfully", $verifiesData);
    }
    public function fileVerifyRequest(VerificationRequest $request)
    {
        
        DB::beginTransaction();
        try {
            $folderName = uniqid() . "_" . time();
            $base_verify_location = public_path("/verification_images");
            $base_verify_file_location = public_path("/verification_images/" . $folderName);
            if (!File::isDirectory($base_verify_location)) {
                File::makeDirectory($base_verify_location, 493, true);
            }
            if (!File::isDirectory($base_verify_file_location)) {
                File::makeDirectory($base_verify_file_location);
            }

            $oldFilePath = "";
            
           
            $checkIfVerifyIsExisting = Verify::where("user_id", Auth::id())->first();
            if ($checkIfVerifyIsExisting) {
                $oldFilePath = $checkIfVerifyIsExisting->file_folder_path;
               
                $checkIfVerifyIsExisting->file_type = $request->verify_type;
                $checkIfVerifyIsExisting->file_folder_path = $folderName;
                $checkIfVerifyIsExisting->file_verify_status = VerifyStatus::PENDING;
                $checkIfVerifyIsExisting->save();

                //delete old image

            } else {
                $newVerify = new Verify();
                $newVerify->file_type = $request->verify_type;
                $newVerify->file_folder_path = $folderName;
                $newVerify->file_verify_status = VerifyStatus::PENDING;
                $newVerify->user_id = Auth::id();
                $newVerify->save();
            }

            





            foreach ($request->images as $image) {
                $fileExtension = $image->getClientOriginalExtension();
                $fileName = $request->verify_type . "-" . uniqid() . "_" . time() . "." . $fileExtension;
                $file_location_with_name = public_path("/verification_images/" . $folderName . "/" . $fileName);
                Image::make($image)->resize(1920, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($file_location_with_name);
            }
            if ($oldFilePath != "") {
                if (File::isDirectory(public_path("/verification_images/".$oldFilePath))) {
                    File::deleteDirectory(public_path("/verification_images/".$oldFilePath));
                }
            }
            DB::commit();
            return $this->success("Save data successfully", 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }


    public function updateVerify(Request $request, $id)
    {
        $request->validate([
            "address_verify_status"=>[ "sometimes",
            Rule::in([VerifyStatus::APPROVED,VerifyStatus::REJECTED,VerifyStatus::PENDING])],
            "file_verify_status"=>[ "sometimes",
            Rule::in([VerifyStatus::APPROVED,VerifyStatus::REJECTED,VerifyStatus::PENDING])],
            "phone_verify_status"=>[ "sometimes",
            Rule::in([VerifyStatus::APPROVED,VerifyStatus::REJECTED,VerifyStatus::PENDING])]
        ]);
        
       
        //receive all request status address and file status
        $verify = Verify::where("id",$id)->with("user")->first();
      
       
        $is_address_verify = false;
        $is_file_verify = false;
        $is_phone_verify = false;
       
        if($request->has("address_verify_status")){
            $verify->address_verify_status = $request->address_verify_status;
            $is_address_verify = true;
        }
        if($request->has("file_verify_status")){
            $verify->file_verify_status = $request->file_verify_status;
            $is_file_verify = true;
        }
        if($request->has("phone_verify_status")){
            $verify->phone_verify_status = $request->phone_verify_status;
            $is_phone_verify = true;
        
        }
        return response()->json(["data"=>[
            "address"=>$is_address_verify,
            "file"=>$is_file_verify,
            "phone"=>$is_phone_verify
        ]]);
    
            $messaging = app('firebase.messaging');
        $androidConfig = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'high',

        ]);
       

    if($is_address_verify){
        $message = CloudMessage::withTarget("topic",$verify->user->topic)
        ->withNotification(Notification::fromArray([
            'title' => 'ຈາກ ຂອງດີ',
            'body' => 'ການຢືນຢັນທີ່ຢູ່ຂອງທ່ານ '.$this->getLaosStringStatus($request->address_verify_status),
            'image' => \public_path("/notification_images/chat.png"),
        ]))
        ->withData([
            'buddhist_id' =>"",
            'type' => 'verify'.$verify->id,
            'sender' => "0",
            'result' => "",
        ]);
    $message = $message->withAndroidConfig($androidConfig);
    $messaging->send($message);
    };
    if($is_file_verify){
        $message = CloudMessage::withTarget("topic",$verify->user->topic)
        ->withNotification(Notification::fromArray([
            'title' => 'ຈາກ ຂອງດີ',
            'body' => 'ການຢືນຢັນ'.$this->getLaosStringFilesStatus($verify->verify_file_type).' ຂອງທ່ານ '.$this->getLaosStringStatus($request->file_verify_status),
            'image' => \public_path("/notification_images/chat.png"),
        ]))
        ->withData([
            'buddhist_id' =>"",
            'type' => 'verify'.$verify->id,
            'sender' => "0",
            'result' => "",
        ]);
    $message = $message->withAndroidConfig($androidConfig);
    $messaging->send($message);
    }
    if($is_phone_verify){
        
        $message = CloudMessage::withTarget("topic",$verify->user->topic)
        ->withNotification(Notification::fromArray([
            'title' => 'ຈາກ ຂອງດີ',
            'body' => 'ການຢືນຢັນເບີໂທຂອງທ່ານ '.$this->getLaosStringStatus($request->phone_verify_status),
            'image' => \public_path("/notification_images/chat.png"),
        ]))
        ->withData([
            'buddhist_id' =>"",
            'type' => 'verify'.$verify->id,
            'sender' => "0",
            'result' => "",
        ]);
    $message = $message->withAndroidConfig($androidConfig);
    $messaging->send($message);
    }

        
     

      $verify->save();
        
      
        
      
        return $this->success("Update verify successfully", 200);
    }

    

    public function viewVerify()
    {
        try {
           // $userWithVerify = User::where("id", Auth::id())->with("verify")->first();
           $verifyWithUser = Verify::where("user_id",Auth::id())->with("user")->first();
            // return response()->json(["data"=>$verifyWithUser]);
            if(!$verifyWithUser){
              return  $this->error("No data found",404);
            }
           
            return new VerifyResource($verifyWithUser);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function adminViewVerify($id)
    {
        $verify = Verify::with("user")->where("id", $id)->first();
        if(!$verify){
            
            return $this->error("No data found",404);
        }
       // return response()->json(["data"=>$verify]);
        return new VerifyResource($verify);
       // return $this->success("get data from", $verify, 200);
    }


    /*public function operateVerification(Request $request, $id)
    {
        //approve the request 
        DB::beginTransaction();
        try {

            $verifyData = Verify::findOrFail($id);
            $currentStatus = $verifyData->status;
            $user = User::find($verifyData->user_id);
            $userStatus = $user->isVerify;

            if ($currentStatus == $request->status) {
                $this->error("Status is ducplicated", 400);
            }
            if ($request->status == VerifyStatus::APPROVED) {
                $userStatus = true;
            } else if ($request->status == VerifyStatus::REJECTED || $request->status == VerifyStatus::PENDING) {
                $userStatus = false;
            }
            $verifyData->status = $request->status;
            $updatedVerifyData = $verifyData->save();

            $user->isVerify = $userStatus;
            $user->save();
            DB::commit();
            return $this->success("Verify data status change", $updatedVerifyData);
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }*/

    public function verifyNumber(Request $request)
    {
        $request->validate([
          "firebase_token"=>"required|string",
            "phone_number"=>"required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10",
            "fcm_token"=>"required|string"
        ]);
        DB::beginTransaction();
        try {

            $phone_number = "";
            $auth = app('firebase.auth');
            $idTokenString = $request->firebase_token;

            try { // Try to verify the Firebase credential token with Google
                $verifiedIdToken = $auth->verifyIdToken($idTokenString);
            } catch (\InvalidArgumentException $e) { // If the token has the wrong format
                return response()->json(
                    [
                        'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage(),
                    ],
                    401
                );
            } catch (InvalidToken $e) { // If the token is invalid (expired ...)

                return response()->json([
                    'message' => 'Unauthorized - Token is invalid: ' . $e->getMessage(),
                ], 401);
            }

            $messaging = app('firebase.messaging');
            $result = $messaging->validateRegistrationTokens($request->fcm_token);
            if ($result['invalid'] != null) {
                return response()->json(['data' => 'your json token is invalid'], 404);
            }

            // Retrieve the UID (User ID) from the verified Firebase credential's token
            $uid = $verifiedIdToken->claims()->get('sub');
            $toDeleteUID = "";
            if (Auth::user()->firebase_uid != $uid) {
                $toDeleteUID = $uid;
            }





            $existingPhoneNumber = User::where("phone_number", Auth::user()->phone_number)->first();
            if ($existingPhoneNumber) {
              return  $this->error("Phone number is exist", 400);
            }


            $user = User::findOrFail(Auth::id());
            $user->phone_number = $request->phone_number;
            //$user->firebase_uid = $uid;
            $user->save();
            $phone_number = $request->phone_number;


            $checkVerify = Verify::where("user_id", Auth::id())->first();
            $showData=null;
            if ($checkVerify) {
                $checkVerify->phone_number = $phone_number;
                $checkVerify->phone_verify_status = VerifyStatus::APPROVED;
                $checkVerify->save();
                $showData=$checkVerify;
            } else {
                $aVerify = new Verify();
                $aVerify->phone_number = $phone_number;
                $aVerify->phone_verify_status = VerifyStatus::APPROVED;
                $aVerify->user_id = Auth::id();
                $aVerify->save();
                $showData=$aVerify;
            }

          

            if($toDeleteUID!=""){
              
                    

                    $forceDeleteEnabledUser=true;
                    $auth->deleteUser($toDeleteUID,$forceDeleteEnabledUser);
                    $properties = [
                        'phoneNumber'=>$request->phone_number,
                    ];
                    $auth->updateUser(Auth::user()->firebase_uid,$properties);
                
                
            }
            DB::commit();
           return response()->json(["data"=>$showData,"message"=>"save message successfully","success"=>true],200);
           
        } catch (\Exception $e) {
            DB::rollback();
            if ($e instanceof \Kreait\Firebase\Exception\Auth\UserNotFound) {
                return response()->json(['error'=>['message'=>'firebase user not found']], 404);
        }
            return $e->getMessage();
        }
    }


    public function verifyPersonalData(Request $request)
    {
        /**
         * name surname gender date of birth address 
         * */
        $request->validate([
            "name" =>"required|string",
            "surname"=>"required|string",
            "gender"=>["required",Rule::in([GenderEnum::FEMALE,GenderEnum::MALE])],
            "date_of_birth"=>"required|date",
           
            "address"=>"required|string",
        ]);
        DB::beginTransaction();
        $user = User::findOrFail(Auth::id());
        try {
            if (!$user) {
               return $this->error("Data not found", 404);
            }
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->gender = $request->gender;
            $user->date_of_birth = $request->date_of_birth;
           
            $user->save();

            $verify = Verify::where("user_id", Auth::id())->first();
            if ($verify) {
                $verify->address = $request->address;
                $verify->address_verify_status = VerifyStatus::PENDING;
                $verify->save();
            } else {
                $verify = new Verify();
                $verify->address = $request->address;
                $verify->address_verify_status = VerifyStatus::PENDING;
                $verify->user_id = Auth::id();
                $verify->save();
            }
            DB::commit();
            return $this->success("Save address successfully", 200);
          
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    public function addEmergencyPhone(Request $request){
        $request->validate([
            "emergency_phone_number"=>"required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10",
        ]);
        $user = User::findOrFail(Auth::id());
        $user->emergency_phone_number = $request->emergency_phone_number;
        $user->save();
        return response()->json(["data"=>$user]);
    }

}
