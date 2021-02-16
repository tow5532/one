<?php

namespace App\Admin\Controllers;

use App\RefundMin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RefundMinController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '출금최소수량설정';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RefundMin());

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

       // $grid->column('id', __('Id'));
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
        $show = new Show(RefundMin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('min_count', __('Min count'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RefundMin());

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
