<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryDeleteLog extends Model
{
    protected $table = 'history_delete_log';

    protected $fillable = [
        'his_idx',
        'delete_id',
        'userid',
        'username',
        'dataType',

    ];



}
