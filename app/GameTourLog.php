<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameTourLog extends Model
{
    protected $connection   = 'mssql';
    protected $table        = 'Log_Tournament';
    protected $primaryKey   = 'idx';


    public function logs()
    {
        return $this->hasMany(GameLog::class, 'Contest_Start_Date', 'Start_Date')
            ->where('channel', 'tournament')
            ->where('GameUniqueID', '=', $this->GameUniqueID)
            ->where('GameRulesID', '=', $this->GameRulesID);
    }

    public function ranks()
    {
        return $this->hasMany(GameTourRank::class, 'TournamentUID', 'GameUniqueID')
            ->where('start_date', $this->Start_Date)
            ->where('tnmt_id', $this->GameRulesID);
    }
}
