<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameSitLog extends Model
{
    protected $connection   = 'mssql';
    protected $table        = 'Log_SitNGo';
    protected $primaryKey   = 'idx';

    public function logs()
    {
        return $this->hasMany(GameLog::class, 'Contest_Start_Date', 'Start_Date')
            ->where('channel', 'sitngo')
            ->where('GameUniqueID', '=', $this->GameUniqueID)
            ->where('GameRulesID', '=', $this->GameRulesID);
    }
}
