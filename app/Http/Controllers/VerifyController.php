<?php

namespace App\Http\Controllers;

use App\Http\Requests\Verification\VerificationRequest;
use App\Models\Verify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\VerifyInterface;

class VerifyController extends Controller
{
  protected $verificationInterface;

  public function __construct(VerifyInterface $verifyInterface)
  {

    $this->middleware("auth:api");
    $this->middleware("isUserActive:api");

    $this->verificationInterface = $verifyInterface;
  }

  public function getAllVerification(Request $request)
  {
    if (Auth::user()->hasRole("admin") || Auth::user()->hasRole("superadmin")) {
      return $this->verificationInterface->getAllverification($request);
    }else{
      return response()->json(["message" => "only user can't use this function"], 403);
    }
   
  }
  public function fileVerifyRequest(VerificationRequest $request)
  {
    return $this->verificationInterface->fileVerifyRequest($request);
  }
  public function updateVerify(Request $request, $id)
  {
    if (Auth::user()->hasRole("admin") || Auth::user()->hasRole("superadmin")) {
      return $this->verificationInterface->updateVerify($request, $id);
    }else{
      return response()->json(["message" => "only user can't use this function"], 403);
    }
    
  }
  public function viewVerify(Request $request)
  {
    return $this->verificationInterface->viewVerify($request);
  }
  public function adminViewVerify($id)
  {
    if (Auth::user()->hasRole("admin") || Auth::user()->hasRole("superadmin")) {
      return $this->verificationInterface->adminViewVerify($id);
    }else{
      return response()->json(["message" => "only user can't use this function"], 403);
    }
    
  }
  public function verifyNumber(Request $request)
  {
    return $this->verificationInterface->verifyNumber($request);
  }
  public function verifyPersonalData(Request $request)
  {
    return $this->verificationInterface->verifyPersonalData($request);
  }

  public function addEmergencyPhone(Request $request){
    return $this->verificationInterface->addEmergencyPhone($request);
  }
}
