<?php

namespace App\Repositories;

use App\Interfaces\VerifyInterface;
use App\Http\Requests\Verification\VerificationRequest;
use App\Traits\ResponseAPI;
use App\Models\Verify;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Image;
use App\Enums\VerifyStatus;
use Illuminate\Http\Request;
use App\Http\Resources\VerifyResource;
use App\Models\User;
use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Support\Facades\Auth;
use App\Enums\GenderEnum;
use App\Enums\VerifyFileType;
use Illuminate\Support\Facades\File;
class VerifyRepository implements VerifyInterface
{
    use ResponseAPI;
    public function getAllVerification(Request $request)
    {
        $optionStatus = "";

        if ($request->has("status") == VerifyStatus::APPROVED) {
            $optionStatus = VerifyStatus::APPROVED;
        } else if ($request->has("status") == VerifyStatus::PENDING) {
            $optionStatus = VerifyStatus::PENDING;
        } else if ($request->has("status") == VerifyStatus::REJECTED) {
            $optionStatus = VerifyStatus::REJECTED;
        }
        $verifiesData = Verify::with("user")->where("status", $optionStatus)->get();
        return $this->success("get data successfully", $verifiesData);
    }
    public function fileVerifyRequest(VerificationRequest $request)
    {
        DB::beginTransaction();
        try {
            $folderName = uniqid() . "_" . time();
            $base_verify_location = public_path("/verification_images");
            $base_verify_file_location = public_path("/verification_images/" . $folderName);
            if (File::isDirectory($base_verify_location)) {
                File::makeDirectory($base_verify_location, 493, true);
            }
            if (File::isDirectory($base_verify_file_location)) {
                File::makeDirectory($base_verify_file_location);
            }

            $oldFilePath = "";
        
            $checkIfVerifyIsExisting = Verify::where("user_id", Auth::id())->get();
            if ($checkIfVerifyIsExisting) {
                $oldFilePath = $checkIfVerifyIsExisting->getImagePath();
                $checkIfVerifyIsExisting->file_type = $request->file_type;
                $checkIfVerifyIsExisting->file_folder_path = $folderName;
                $checkIfVerifyIsExisting->file_verify_status = VerifyStatus::PENDING;
                $checkIfVerifyIsExisting->save();

                //delete old image
              
            }else{
                $newVerify = new Verify();
                $newVerify->file_type = $request->verify_file_type;
                $newVerify->file_folder_path = $folderName;
                $newVerify->file_verify_status = VerifyStatus::PENDING;
                $newVerify->user_id = Auth::id();
                $newVerify->save();
            }

           
            



          
            foreach ($request->images as $image) {
                $fileExtension = $image->getClientOriginalExtension();
                $fileName = $request->verify_type . "-" . uniqid() . "_" . time() . "." . $fileExtension;
                $file_location_with_name = public_path("/verification_images/" . $folderName . "/" . $fileName);
                Image::make($image)->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($file_location_with_name);
            }
            if($oldFilePath!=""){
                if (File::isDirectory($oldFilePath)) {
                    File::deleteDirectory($oldFilePath);
                }
            }
            DB::commit();
            return $this->success("Save data successfully", 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }


    public function updateVerify(Request $request,$id)
    {
        //receive all request status address and file status
        $verify = Verify::findOrFail($id);
        $verify->address_verify_status = $request->address_verify_status;
        $verify->phone_number_verify_status = $request->phone_number_verify_status;
        $verify->save();
        return $this->success("Update verify successfully", 200);
    }

    public function viewVerify()
    {

        try {
            $userWithVerify = User::where("id", Auth::id())->with("verify")->get();
            return new VerifyResource($userWithVerify);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function adminViewVerify($id){
        $verify = Verify::with("user")->where("id",$id)->get();
        return $this->success("get data from",$verify,200);
    }


    public function operateVerification(Request $request, $id)
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
    }

    public function verifyNumber(Request $request)
    {
        DB::startTransaction();
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
            if (Auth::user()->firebase_uid == $uid) {
                $phone_number = Auth::user()->phone_number;
            } else {

                $toDeleteUID = Auth::user()->firebase_uid;



                $existingPhoneNumber = User::where("phone_number", Auth::user()->phone_number)->first();
                if ($existingPhoneNumber) {
                    $this->error("Phone number is exist", 400);
                }


                $user = User::findOrFail(Auth::id());
                $user->phone_number = $request->phone_number;
                $user->firebase_uid = $uid;
                $user->save();
                $phone_number = $request->phone_number;
            }

            $checkVerify = Verify::where("user_id", Auth::id())->first();
            if ($checkVerify) {
                $checkVerify->phone_number = $phone_number;
                $checkVerify->phone_number_verify_status = VerifyStatus::APPROVED;
                $checkVerify->save();
            } else {
                $aVerify = new Verify();
                $aVerify->phone_number = $phone_number;
                $aVerify->phone_number_verify_status = VerifyStatus::APPROVED;
                $aVerify->user_id = Auth::id();
                $aVerify->save();
            }

            $forceDeleteEnabledUser = true;
            $auth->deleteUsers([$uid], $forceDeleteEnabledUser);

            $this->success("Phone verify successfully", 200);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }


    public function verifyPersonalData(Request $request)
    {
        /**
         * name surname gender date of birth address 
         * */
        DB::startTransaction();
        $user = User::findOrFail(Auth::id());
        try {
            if (!$user) {
                $this->error("Data not found", 400);
            }
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->gender = $request->gender;
            $user->date_of_birth = $request->date_of_birth;
            $user->emergency_phone_number = $request->emergency_phone_number;
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
            return $this->success("Save address successfully", 200);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }
}
