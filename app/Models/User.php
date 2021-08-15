<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\LaratrustUserTrait;
use Laravel\Passport\HasApiTokens;

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
        'name', 'surname', 'email', 'password', 'phone_number', 'picture', 'firebase_uid',
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
        'password', 'remember_token', 'firebase_uid', 'email_verified_at',
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
        return $this->where('phone_number', $phone_number)->first();
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

    public function getProfilePath()
    {
        return Config("values.APP_URL") . ":" . $_SERVER["SERVER_PORT"] .
        "/" . "profile_image/" . $this->picture;
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
}
