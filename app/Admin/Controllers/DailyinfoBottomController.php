<?php

namespace App\Admin\Controllers;

use App\DailyinfoBottom;
use App\Recommend;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use http\Client\Request;

class DailyinfoBottomController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DailyinfoBottom';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DailyinfoBottom());

        $level = 'sub_company';

        $grid->model()->where('level', '=', $level);

        //어드민 계정이 아닐경우 조건문 을 추가 한다.
        if (!Admin::user()->isRole('administrator') && !Admin::user()->isRole('master') && !Admin::user()->isRole('company')) {
            $grid->model()->where('user_id', Admin::user()->id);
        } else {
            $grid->model()->groupBy('user_id');
        }

        $grid->model()->select('user_id');


        $grid->column('user_id', 'User ID');

        $grid->column('Total User payment')->display(function (){
                $payment = DailyinfoBottom::where('user_id', $this->user_id)
                    ->sum('total_payment');
                return number_format($payment);
        });

        $grid->column('Total User exchange')->display(function (){
            $refund = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('total_refund');
            return number_format($refund);
        });

        /*
        $grid->column('id', 'Id');
        $grid->column('search_date', __('Search date'));
        $grid->column('level', __('Level'));
        $grid->column('user_id', __('User id'));
        $grid->column('username', __('Username'));
        $grid->column('total_payment', __('Total payment'));
        $grid->column('total_refund', __('Total refund'));
        $grid->column('user_chips', __('User chips'));
        $grid->column('user_safe', __('User safe'));
        $grid->column('user_deposit', __('User deposit'));
        $grid->column('rev', __('Rev'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        */


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
        $show = new Show(DailyinfoBottom::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('search_date', __('Search date'));
        $show->field('level', __('Level'));
        $show->field('user_id', __('User id'));
        $show->field('username', __('Username'));
        $show->field('total_payment', __('Total payment'));
        $show->field('total_refund', __('Total refund'));
        $show->field('user_chips', __('User chips'));
        $show->field('user_safe', __('User safe'));
        $show->field('user_deposit', __('User deposit'));
        $show->field('rev', __('Rev'));
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
        $form = new Form(new DailyinfoBottom());

        $form->date('search_date', __('Search date'))->default(date('Y-m-d'));
        $form->text('level', __('Level'));
        $form->number('user_id', __('User id'));
        $form->text('username', __('Username'));
        $form->text('total_payment', __('Total payment'));
        $form->text('total_refund', __('Total refund'));
        $form->text('user_chips', __('User chips'));
        $form->text('user_safe', __('User safe'));
        $form->text('user_deposit', __('User deposit'));
        $form->text('rev', __('Rev'));

        return $form;
    }
}
