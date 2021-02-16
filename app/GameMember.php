<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameMember extends Model
{
    protected $connection   = 'mssql';
    protected $table        = 'Player';
    protected $primaryKey   = 'AccountUniqueid';

    //public $incrementing = false;
    //public $timestamps = false;

    public function account()
    {
        return $this->hasOne(GameAccount::class, 'AccountIDx', 'AccountUniqueid');
    }
    public function user()
    {
        return $this->hasOne(User::class, 'account_id', 'AccountUniqueid');
    }

    public function logmoneys()
    {
        return $this->hasMany(LogMoney::class, 'AccountUniqueID', 'AccountUniqueid');
    }
}
