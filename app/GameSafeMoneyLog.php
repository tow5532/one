<?php
/**
 * Created by PhpStorm.
 * User: YONGMAN LEE
 * Date: 2020-07-29
 * Time: 오후 10:19
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class GameSafeMoneyLog extends Model
{
    protected $table = 'game_safe_money_log';

    protected $fillable = [
        'id',
        'user_id',
        'safer_id',
        'safe_money',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}