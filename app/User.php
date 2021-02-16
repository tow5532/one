<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection   = 'mysql';
    protected $fillable = [
        'id',
        'username',
        'name',
        'email',
        'password',
        'confirm_code',
        'activated',
        'avatar',
        'temp_password',
        'bank',
        'account',
        'holder',
        'withdrawal_password',
        'phone',
        'facebook',
        'deposit_amount',
        'admin_yn',
        'telegram_id',
        'losing_profit',
        'rolling_profit',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
    public function points()
    {
        return $this->hasMany(Point::class);
    }
    public function player()
    {
        return $this->hasOne(GameMember::class, 'AccountUniqueid', 'account_id');
    }
    public function recommend()
    {
        return $this->hasOne(Recommend::class, 'id', 'user_id');
    }
    public function logmoneys()
    {
        return $this->hasMany(LogMoney::class, 'AccountUniqueID', 'account_id');
    }
    public function routeNotificationForTelegram()
    {
        return $this->telegram_id;
    }

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }

    public function losingCompany()
    {
        return $this->hasMany(DailyInfoLosing::class, 'com_id', 'id');
    }

    public function losingSub()
    {
        return $this->hasMany(DailyInfoLosing::class, 'sub_id', 'id');
    }

    public function losingDist()
    {
        return $this->hasMany(DailyInfoLosing::class, 'dist_id', 'id');
    }

    public function losingStore()
    {
        return $this->hasMany(DailyInfoLosing::class, 'store_id', 'id');
    }

    public function losingTotalCompany()
    {
        return $this->hasMany(DailyInfoLosingTotal::class, 'user_id', 'id');
    }

    public function headquarters()
    {
        return $this->hasMany(HeadquarterLog::class, 'user_id', 'id');
    }

}
