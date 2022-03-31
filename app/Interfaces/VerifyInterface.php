<?php
namespace App\Interfaces;
use App\Http\Requests\Verification\VerificationRequest;
use Illuminate\Http\Request;


interface VerifyInterface{   
    public function getAllVerification (Request $request);
    public function fileVerifyRequest (VerificationRequest $request);
    public function updateVerify  (Request $request, $id);
    public function viewVerify ();
    public function adminViewVerify ($id);
    public function verifyNumber (Request $request);
    public function verifyPersonalData (Request $request);
}

