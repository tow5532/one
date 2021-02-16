<?php

namespace App\Admin\Actions\Link;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Replicate extends RowAction
{
    public $name = '하부보기';

    public function href()
    {
        $this->getResource();
    }

}
