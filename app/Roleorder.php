<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roleorder extends Model
{
    protected $table = 'admin_roles_order';

    protected $fillable = [
        'roles_id',
        'orderby',
    ];
}
