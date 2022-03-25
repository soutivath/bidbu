<?php

namespace App\Http\Controllers;

use App\Http\Requests\Verification\VerificationRequest;
use App\Models\Verify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Interfaces\VerifyInterface;
class VerifyController extends Controller
{
  protected $verificationInterface;

    public function __construct(VerifyInterface $verifyInterface){
      $this->verificationInterface = $verifyInterface;
    }

    public function requestVerify(VerificationRequest $request)
    {
      
    }

    public function getAllVerify(Request $request){

      return $this->verificationInterface->getAllVerification();
    }
    public function viewVerify(Request $request){

    }

    public function operateVerification(Request $request){
    }



    
}
