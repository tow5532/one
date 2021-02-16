<?php

namespace App\Admin\Controllers;

use App\GameInfo;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class GameInfoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '게임머니변환설정';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GameInfo());
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            $actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });
        $grid->disableCreateButton();

        $grid->column('id', trans('admin.deposit.no'));
        $grid->column('name', trans('admin.game_setting.name'));
        $grid->column('code', trans('admin.game_setting.code'));
        $grid->column('inquote', trans('admin.game_setting.inquote'));
        $grid->column('outquote', trans('admin.game_setting.outquote'));
        $grid->column('min_amount', trans('admin.game_setting.min_amount'));
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
        $show = new Show(GameInfo::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('code', __('Code'));
        $show->field('inquote', __('Inquote'));
        $show->field('outquote', __('Outquote'));
        $show->field('min_amount', __('Min amount'));
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
        $form = new Form(new GameInfo());

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

        $form->display('name', trans('admin.game_setting.name'));
        $form->display('code', trans('admin.game_setting.code'));
        $form->text('inquote', trans('admin.game_setting.inquote'));
        $form->text('outquote', trans('admin.game_setting.outquote'));
        $form->text('min_amount', trans('admin.game_setting.min_amount'));

        return $form;
    }
}
