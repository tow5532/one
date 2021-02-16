<?php

namespace App\Admin\Controllers;

use App\Resources;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ResourceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Resource';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Resources());

        $grid->column('id', trans('admin.resource.id'));
        $grid->column('category', trans('admin.resource.category'));
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

        $show->field('id', trans('admin.resource.id'));
        $show->field('category', trans('admin.resource.category'));
        $show->field('title', trans('admin.resource.title'));
        $show->field('title_img', trans('admin.resource.title_img'))->image('/upload/', 300, 300);;
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
        $form = new Form(new Resources());

        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $form->select('category', trans('admin.resource.category'))
            ->options(['event' => 'event'])
            ->rules('required');

        $form->text('title', trans('admin.resource.title'))->rules([
            'required',
            'max:50',
        ]);

        $form->image('title_img', trans('admin.resource.title_img'))
            ->removable()
            ->uniqueName()
            ->rules('required');

        $form->ckeditor('content', trans('admin.resource.content'))->rules('required');

        return $form;
    }
}
