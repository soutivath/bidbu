<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\LaratrustUserTrait;
use Laravel\Passport\HasApiTokens;
use App\Enums\VerifyStatus;
class User extends Authenticatable
{
    use LaratrustUserTrait;
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'surname', 'email', 'password', 'phone_number', 'picture', 'firebase_uid','gender','date_of_birth',"emergency_phone_number"
    ];
    public function getAuthPassword()
    {
        return $this->password;
    }

    protected $primaryKey = 'id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'firebase_uid', 'email_verified_at','topic','coin'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function findForPassport($phone_number)
    {
        //return $this->where('phone_number', $phone_number)->first();
        if(filter_var($phone_number, FILTER_VALIDATE_EMAIL)){
         return  $this->where('email_address', $phone_number)->first();
        }
        else if(is_numeric($phone_number)){
            return $this->where('phone_number', $phone_number)->first();
        }
        else{
            return $this->where('firebase_uid', $phone_number)->first();
        }

       // return $this->where(fn($q) => $q->where('email', $username)->orWhere('phone', $username));
    }

    public function favourites()
    {
        return $this->hasMany('app\favourite');
    }
    public function buddhists()
    {
        return $this->hasMany("app\Buddhist", "user_id");
    }

    public function notifications()
    {
        return $this->hasMany(NotificationFirebase::class);
    }
    public function verify()
    {
        return $this->hasOne(Verify::class);
    }

    public function getProfilePath()
    {
        
        if(str_starts_with($this->picture,"https://")){
            return $this->picture;
        }else{
            return Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
        "/" . "profile_image/" . $this->picture;
        }
    }

    public function isActiveAdmin()
    {
        if ($this->hasRole(["superadmin", "admin"]) && $this->active == 1) {
            return true;
        } else {
            return false;
        }

    }

    public function isUserActive()
    {
        if ($this->active == 1) {
            return true;
        } else {
            return false;
        }
    }
    public function getVerifyStatus(){
        if($this->verify()->exists()){
            if($this->verify->file_verify_status==VerifyStatus::APPROVED||$this->file_verify_status==VerifyStatus::PENDING){
                return true;
            }
            return false;
        }else{
            return false;
        }
    }
}
