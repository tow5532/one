<?php

namespace App\Admin\Controllers;

use App\LogMoney;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LogMoneyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LogMoney';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LogMoney());

        $grid->column('idx', __('Idx'));
        $grid->column('AccountUniqueID', __('AccountUniqueID'));
        $grid->column('Own_Money', __('Own Money'));
        $grid->column('Fluctuation_money', __('Fluctuation money'));
        $grid->column('currently_money', __('Currently money'));
        $grid->column('Fluctuation_reason', __('Fluctuation reason'));
        $grid->column('Fluctuation_date', __('Fluctuation date'));
        $grid->column('comment', __('Comment'));

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
        $show = new Show(LogMoney::findOrFail($id));

        $show->field('idx', __('Idx'));
        $show->field('AccountUniqueID', __('AccountUniqueID'));
        $show->field('Own_Money', __('Own Money'));
        $show->field('Fluctuation_money', __('Fluctuation money'));
        $show->field('currently_money', __('Currently money'));
        $show->field('Fluctuation_reason', __('Fluctuation reason'));
        $show->field('Fluctuation_date', __('Fluctuation date'));
        $show->field('comment', __('Comment'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LogMoney());

        $form->number('AccountUniqueID', __('AccountUniqueID'));
        $form->text('Own_Money', __('Own Money'));
        $form->text('Fluctuation_money', __('Fluctuation money'));
        $form->text('currently_money', __('Currently money'));
        $form->number('Fluctuation_reason', __('Fluctuation reason'));
        $form->datetime('Fluctuation_date', __('Fluctuation date'))->default(date('Y-m-d H:i:s'));
        $form->text('comment', __('Comment'))->default('none');

        return $form;
    }
}
