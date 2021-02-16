<?php
/**
 * Created by PhpStorm.
 * User: YONGMAN LEE
 * Date: 2020-07-29
 * Time: 오후 10:19
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class TSafer extends Model
{
    protected $connection = 'mssql';
    protected $table = 't_safer';

    protected $fillable = [
        'AccountuniqueID',
        'safe_money',
        'flag',
    ];
    protected $dates = [
        'logdate',
        'update',
    ];
}