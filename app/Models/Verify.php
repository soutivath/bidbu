<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Enums\VerifyStatus;
class Verify extends Model
{
    use HasFactory;

    protected $fillable = ["address","address_verify_status","phone_number","phone_verify_status","file_type","file_folder_path","file_verify_status","user_id"];
    
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getImagePath(){
       
        return file(base_path("resources/private/verify"));
        return Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
        "/" . "verification_images/" . $this->file_folder_path;
    }

    public function checkIfVerifyFile(){
       
        if($this->file_verify_status==VerifyStatus::APPROVED){
            return true;
        }
        return false;
    }
}
