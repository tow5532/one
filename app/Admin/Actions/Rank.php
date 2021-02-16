<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Rank extends RowAction
{
    public $name = 'rank';

    public function handle(Model $model)
    {
        // $model ...
        $key = $model->getKey();
        //return $this->response()->success('Success message.')->refresh();
    }

}
