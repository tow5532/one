<?php

namespace App\Admin\Controllers;

use App\BankAccount;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BankController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '은행정보';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new BankAccount());

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            $actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });
        $grid->disableCreateButton();

        /*$grid->column('id', trans('admin.deposit.no'));
        //$grid->column('user_id', __('User id'));
        $grid->user_id('ADMIN ID')->display(function ($user_id){
            return User::find($user_id)->value('username');
        });*/
        $grid->column('bank', trans('admin.deposit.bank'));
        $grid->column('account', trans('admin.deposit.bank_account'));
        $grid->column('holder', trans('admin.deposit.holder'));
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
        $show = new Show(BankAccount::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('bank', __('Bank'));
        $show->field('account', __('Account'));
        $show->field('holder', __('Holder'));
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
        $form = new Form(new BankAccount());

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

        $form->hidden('user_id')->value(Admin::user()->id);
        $form->text('bank', __('Bank'))->rules('required');
        $form->text('account', __('Account'))->rules('required');
        $form->text('holder', __('Holder'))->rules('required');

        return $form;
    }
}
