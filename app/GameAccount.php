<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameAccount extends Model
{
    protected $connection   = 'mssql';
    protected $table        = 'Account';
    protected $primaryKey   = 'AccountIDx';

    public $timestamps = false;

    public function play()
    {
        return $this->hasOne(GameMember::class, 'AccountUniqueid', 'AccountIDx');
    }
}
