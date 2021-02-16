<?php

namespace App\Admin\Controllers;

use App\Refundquote;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RefundQuoteController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '출금수량비율';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Refundquote());

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            $actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });
        $grid->disableCreateButton();

        //$grid->column('id', __('Id'));
        $grid->column('amount', trans('admin.inquote.amount'));
        $grid->column('price', trans('admin.inquote.price'));
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
        $show = new Show(Refundquote::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('amount', __('Amount'));
        $show->field('price', __('Price'));
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
        $form = new Form(new Refundquote());

        $form->tools(function (Form\Tools $tools) {
            //$tools->disableList();
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $form->display('amount', trans('admin.inquote.amount'));
        $form->text('price', trans('admin.inquote.price'));

        return $form;
    }
}
