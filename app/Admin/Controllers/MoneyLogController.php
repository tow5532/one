<?php

namespace App\Admin\Controllers;

use App\MoneyLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MoneyLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MoneyLog';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*$grid = new Grid(new MoneyLog());

        $grid->column('id', __('Id'));
        $grid->column('level', __('Level'));
        $grid->column('user_id', __('User id'));
        $grid->column('username', __('Username'));
        $grid->column('search_date', __('Search date'));
        $grid->column('rev_amount', __('Rev amount'));
        $grid->column('chg_amount', __('Chg amount'));
        $grid->column('now_amount', __('Now amount'));
        $grid->column('reason', __('Reason'));
        $grid->column('refund_id', __('Refund id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;*/
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        /*$show = new Show(MoneyLog::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('level', __('Level'));
        $show->field('user_id', __('User id'));
        $show->field('username', __('Username'));
        $show->field('search_date', __('Search date'));
        $show->field('rev_amount', __('Rev amount'));
        $show->field('chg_amount', __('Chg amount'));
        $show->field('now_amount', __('Now amount'));
        $show->field('reason', __('Reason'));
        $show->field('refund_id', __('Refund id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;*/
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        /*$form = new Form(new MoneyLog());

        $form->text('level', __('Level'));
        $form->number('user_id', __('User id'));
        $form->text('username', __('Username'));
        $form->date('search_date', __('Search date'))->default(date('Y-m-d'));
        $form->text('rev_amount', __('Rev amount'));
        $form->text('chg_amount', __('Chg amount'));
        $form->text('now_amount', __('Now amount'));
        $form->text('reason', __('Reason'));
        $form->number('refund_id', __('Refund id'));

        return $form;*/
    }
}
