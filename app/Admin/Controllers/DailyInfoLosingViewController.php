<?php

namespace App\Admin\Controllers;

use App\DailyInfoLosing;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DailyInfoLosingViewController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DailyInfoLosing';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DailyInfoLosing());

        $grid->column('id', __('Id'));
        $grid->column('search_date', __('Search date'));
        $grid->column('user_id', __('User id'));
        $grid->column('username', __('Username'));
        $grid->column('total_deposit', __('Total deposit'));
        $grid->column('total_refund', __('Total refund'));
        $grid->column('total_point', __('Total point'));
        $grid->column('term_point', __('Term point'));
        $grid->column('past_user_point', __('Past user point'));
        $grid->column('user_losing', __('User losing'));
        $grid->column('store_id', __('Store id'));
        $grid->column('store_commission', __('Store commission'));
        $grid->column('store_losing', __('Store losing'));
        $grid->column('dist_id', __('Dist id'));
        $grid->column('dist_commission', __('Dist commission'));
        $grid->column('dist_commission_final', __('Dist commission final'));
        $grid->column('dist_losing', __('Dist losing'));
        $grid->column('sub_id', __('Sub id'));
        $grid->column('sub_commission', __('Sub commission'));
        $grid->column('sub_commission_final', __('Sub commission final'));
        $grid->column('sub_losing', __('Sub losing'));
        $grid->column('com_id', __('Com id'));
        $grid->column('com_commission', __('Com commission'));
        $grid->column('com_commission_final', __('Com commission final'));
        $grid->column('com_losing', __('Com losing'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(DailyInfoLosing::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('search_date', __('Search date'));
        $show->field('user_id', __('User id'));
        $show->field('username', __('Username'));
        $show->field('total_deposit', __('Total deposit'));
        $show->field('total_refund', __('Total refund'));
        $show->field('total_point', __('Total point'));
        $show->field('term_point', __('Term point'));
        $show->field('past_user_point', __('Past user point'));
        $show->field('user_losing', __('User losing'));
        $show->field('store_id', __('Store id'));
        $show->field('store_commission', __('Store commission'));
        $show->field('store_losing', __('Store losing'));
        $show->field('dist_id', __('Dist id'));
        $show->field('dist_commission', __('Dist commission'));
        $show->field('dist_commission_final', __('Dist commission final'));
        $show->field('dist_losing', __('Dist losing'));
        $show->field('sub_id', __('Sub id'));
        $show->field('sub_commission', __('Sub commission'));
        $show->field('sub_commission_final', __('Sub commission final'));
        $show->field('sub_losing', __('Sub losing'));
        $show->field('com_id', __('Com id'));
        $show->field('com_commission', __('Com commission'));
        $show->field('com_commission_final', __('Com commission final'));
        $show->field('com_losing', __('Com losing'));
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
        $form = new Form(new DailyInfoLosing());

        $form->date('search_date', __('Search date'))->default(date('Y-m-d'));
        $form->number('user_id', __('User id'));
        $form->text('username', __('Username'));
        $form->text('total_deposit', __('Total deposit'));
        $form->text('total_refund', __('Total refund'));
        $form->text('total_point', __('Total point'));
        $form->text('term_point', __('Term point'));
        $form->text('past_user_point', __('Past user point'));
        $form->text('user_losing', __('User losing'));
        $form->number('store_id', __('Store id'));
        $form->text('store_commission', __('Store commission'));
        $form->text('store_losing', __('Store losing'));
        $form->number('dist_id', __('Dist id'));
        $form->text('dist_commission', __('Dist commission'));
        $form->text('dist_commission_final', __('Dist commission final'));
        $form->text('dist_losing', __('Dist losing'));
        $form->number('sub_id', __('Sub id'));
        $form->text('sub_commission', __('Sub commission'));
        $form->text('sub_commission_final', __('Sub commission final'));
        $form->text('sub_losing', __('Sub losing'));
        $form->number('com_id', __('Com id'));
        $form->text('com_commission', __('Com commission'));
        $form->text('com_commission_final', __('Com commission final'));
        $form->text('com_losing', __('Com losing'));

        return $form;
    }
}
