<?php

namespace App\Admin\Controllers;

use App\DepositMin;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DepositMinController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '충전최소수량설정';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        //Permission::check('deposit');

        $grid = new Grid(new DepositMin());

        //$grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            $actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });
        $grid->disableCreateButton();

        $grid->column('id', __('Id'));
        $grid->column('min_count', trans('admin.deposit_min.min_amount'))->display(function ($min_count){
            return number_format($min_count);
        });
        $grid->column('created_at', trans('admin.deposit.created_at'));
        $grid->column('updated_at', trans('admin.deposit.updated_at'));



        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        //Permission::check('deposit');

        $show = new Show(DepositMin::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
       // Permission::check('deposit');

        $form = new Form(new DepositMin());

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $form->text('min_count', trans('admin.deposit_min.min_amount'))->rules('required');

        return $form;
    }
}
