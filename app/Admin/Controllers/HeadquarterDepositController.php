<?php

namespace App\Admin\Controllers;

use App\Headquarter;
use App\HeadquarterDeposit;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class HeadquarterDepositController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Headquarter Deposits';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new HeadquarterDeposit());

        $grid->actions(function ($actions) {
           // $actions->disableDelete();
           // $actions->disableEdit();
        });
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
            $filter->expand();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', trans('admin.headquarter_deposit.no'));

        $grid->column('head_id', trans('admin.headquarter_deposit.head_id'))->display(function ($head_id){
            $header = Headquarter::find($head_id);
            if ($header) {
                $user = User::where('id', $header->user_id)->first();
                return 'No : ' . $head_id . '<br>'
                    . 'user_id : ' . $user->username . '<br>'
                    . 'pay Amount : ' . number_format($header->amount);
            }
        });

        $grid->column('deposit_point', trans('admin.headquarter_deposit.deposit_amount'))->display(function ($deposit_point){
            return number_format($deposit_point);
        });

        $grid->column('created_at', trans('admin.headquarter_deposit.created_at'));

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
        $show = new Show(HeadquarterDeposit::findOrFail($id));

        $show->field('id', trans('admin.headquarter_deposit.no'));
        $show->field('head_id', trans('admin.headquarter_deposit.head_id'));
        $show->field('deposit_point', trans('admin.headquarter_deposit.deposit_amount'));
        $show->field('created_at', trans('admin.headquarter_deposit.created_at'));
        $show->field('updated_at', trans('admin.headquarter_deposit.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new HeadquarterDeposit());

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

        $heads = Headquarter::all();
        $steapArr = array();

        foreach ($heads as $head) {
            $user_id = User::where('id', $head->user_id)->first();
            $steapArr[$head->id] = 'NO : ' . $head->id . ' | ID : ' . $user_id->username;
        }

        $form->select('head_id', trans('admin.headquarter_deposit.head_id'))->options($steapArr)->rules('required');

        $form->text('deposit_point', trans('admin.headquarter_deposit.deposit_amount'))->rules('required');

        $form->saved(function (Form $form) {
            $head_amount = Headquarter::where('id', $form->model()->head_id)->first();
            $total_deposit = HeadquarterDeposit::where('head_id', $form->model()->head_id)->sum('deposit_point');

            if ((int)$head_amount->amount === (int)$total_deposit || (int)$head_amount->amount < (int)$total_deposit){
                $deposit = Headquarter::find($form->head_id);
                $deposit->full_ok = '1';
                $deposit->save();
            }
        });

        return $form;
    }
}
