<?php

namespace App\Admin\Controllers;

use App\Resources;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ResourceViewController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '관리자 공지사항';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Resources());

        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableFilter();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            //$actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->model()->where('category', 'admin');
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', trans('admin.resource.id'));
        $grid->column('category', trans('admin.resource.category'))->display(function ($category){
            return ($category === 'notice') ? '일반공지' : '관리자공지';
        });
        $grid->column('title', trans('admin.resource.title'));
        $grid->column('created_at', trans('admin.resource.created_at'));
        $grid->column('updated_at', trans('admin.resource.updated_at'));

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
        $show = new Show(Resources::findOrFail($id));

        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                // $tools->disableList();
                $tools->disableDelete();
            });

        $show->field('id', trans('admin.resource.id'));
        $show->field('category', trans('admin.resource.category'));
        $show->field('title', trans('admin.resource.title'));
        $show->field('content', trans('admin.resource.content'))->json();
        $show->field('created_at', trans('admin.resource.created_at'));
        $show->field('updated_at', trans('admin.resource.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        /*$form = new Form(new Resources());

        $form->text('category', __('Category'))->default('event');
        $form->text('title', __('Title'));
        $form->text('title_img', __('Title img'));
        $form->textarea('content', __('Content'));
        $form->text('content_img', __('Content img'));
        $form->text('content_link', __('Content link'));

        return $form;*/
    }
}
