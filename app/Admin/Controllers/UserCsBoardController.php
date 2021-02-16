<?php

namespace App\Admin\Controllers;

use App\UserCsBoard;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserCsBoardController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'UserCsBoard';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserCsBoard());

        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            $actions->disableView();
        });

        $grid->model()->orderBy('usercsidx', 'desc');

        $grid->column('usercsidx', trans('admin.user_cs.no'));
        $grid->column('username', trans('admin.user_cs.user_id'));
        $grid->column('title', trans('admin.user_cs.title'));
        $grid->column('regdate', trans('admin.user_cs.regdate'));
        $grid->column('updatedate', trans('admin.user_cs.update'));
        $grid->column('delete', trans('admin.user_cs.delete'))->display(function ($delete){
            return ($delete === 0) ? 'N' : 'Y';
        });

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
        $show = new Show(UserCsBoard::findOrFail($id));

        $show->field('usercsidx', __('Usercsidx'));
        $show->field('username', __('Username'));
        $show->field('title', __('Title'));
        $show->field('contents', __('Contents'));
        $show->field('regdate', __('Regdate'));
        $show->field('updatedate', __('Updatedate'));
        $show->field('delete', __('Delete'));
        $show->field('ans_title', __('Ans title'));
        $show->field('ans_contents', __('Ans contents'));
        $show->field('ans_regdate', __('Ans regdate'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserCsBoard());

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

        $form->display('username', trans('admin.user_cs.user_id'));
        $form->text('title', trans('admin.user_cs.title'));
        $form->textarea('contents', trans('admin.user_cs.contents'));
        $form->switch('delete', trans('admin.user_cs.delete'));
        $form->text('ans_title', trans('admin.user_cs.ans_title'));
        $form->textarea('ans_contents', trans('admin.user_cs.ans_contents'));
        $form->datetime('ans_regdate', trans('admin.user_cs.ans_regdate'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
