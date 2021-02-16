<?php
/**
 * Created by PhpStorm.
 * User: YONGMAN LEE
 * Date: 2020-07-29
 * Time: 오후 10:19
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class GameSafeMoney extends Model
{
    protected $table = 'game_safe_money';

    protected $fillable = [
        'id',
        'user_id',
        'safe_money',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}