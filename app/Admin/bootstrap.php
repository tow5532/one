<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use App\Admin\Extensions\Form\CKEditor;

Encore\Admin\Form::forget(['map', 'editor']);

Form::extend('ckeditor', CKEditor::class);

//기본 테이블 CSS 변경
//Admin::style('table > thead {background:#D8D8D8;}');
Admin::style('table > thead > tr > th {text-align:center;}');
Admin::style('table > tbody > tr:nth-child(even) {background:#F6F5F5;}');
Admin::style('table > tbody > tr > td {text-align:center;border-left: solid 1px #EFEFEF;}');

Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {
    $navbar->right(new \App\Admin\Extensions\Nav\EggCount());
});
