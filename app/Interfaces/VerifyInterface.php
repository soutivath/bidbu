<?php
namespace App\Interfaces;
use App\Http\Requests\Verification\VerificationRequest;
use Illuminate\Http\Request;


interface VerifyInterface{
    public function getAllVerification(Request $request);
   // public function requestVerify(VerificationRequest $request);
    public function viewVerify();
    public function operateVerification(Request $request,$id);
}

