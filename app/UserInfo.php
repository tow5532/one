<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Hoyvoy\CrossDatabase\Eloquent\Model;
class UserInfo extends Model
{
    protected $table = 'user_info';
    protected $connection   = 'mysql';

    protected $fillable = [
        'user_id',
        'bank',
        'account',
        'holder',
        'withdrawal_password',
        'phone',
        'facebook',
        'refer_id',
        'account_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
