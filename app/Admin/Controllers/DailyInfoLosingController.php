<?php

namespace App\Admin\Controllers;

use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DailyInfoLosingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->column('id', __('Id'));
        $grid->column('username', __('Username'));
        $grid->column('name', __('Name'));
        $grid->column('email', __('Email'));
        $grid->column('email_verified_at', __('Email verified at'));
        $grid->column('password', __('Password'));
        $grid->column('remember_token', __('Remember token'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('avatar', __('Avatar'));
        $grid->column('profit', __('Profit'));
        $grid->column('losing_profit', __('Losing profit'));
        $grid->column('rolling_profit', __('Rolling profit'));
        $grid->column('temp_password', __('Temp password'));
        $grid->column('bank', __('Bank'));
        $grid->column('account', __('Account'));
        $grid->column('holder', __('Holder'));
        $grid->column('withdrawal_password', __('Withdrawal password'));
        $grid->column('phone', __('Phone'));
        $grid->column('facebook', __('Facebook'));
        $grid->column('activated', __('Activated'));
        $grid->column('confirm_code', __('Confirm code'));
        $grid->column('deposit_amount', __('Deposit amount'));
        $grid->column('admin_yn', __('Admin yn'));
        $grid->column('account_id', __('Account id'));
        $grid->column('telegram_id', __('Telegram id'));
        $grid->column('game_token', __('Game token'));
        $grid->column('game_auth_update', __('Game auth update'));

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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        $show->field('email_verified_at', __('Email verified at'));
        $show->field('password', __('Password'));
        $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('avatar', __('Avatar'));
        $show->field('profit', __('Profit'));
        $show->field('losing_profit', __('Losing profit'));
        $show->field('rolling_profit', __('Rolling profit'));
        $show->field('temp_password', __('Temp password'));
        $show->field('bank', __('Bank'));
        $show->field('account', __('Account'));
        $show->field('holder', __('Holder'));
        $show->field('withdrawal_password', __('Withdrawal password'));
        $show->field('phone', __('Phone'));
        $show->field('facebook', __('Facebook'));
        $show->field('activated', __('Activated'));
        $show->field('confirm_code', __('Confirm code'));
        $show->field('deposit_amount', __('Deposit amount'));
        $show->field('admin_yn', __('Admin yn'));
        $show->field('account_id', __('Account id'));
        $show->field('telegram_id', __('Telegram id'));
        $show->field('game_token', __('Game token'));
        $show->field('game_auth_update', __('Game auth update'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('username', __('Username'));
        $form->text('name', __('Name'));
        $form->email('email', __('Email'));
        $form->datetime('email_verified_at', __('Email verified at'))->default(date('Y-m-d H:i:s'));
        $form->password('password', __('Password'));
        $form->text('remember_token', __('Remember token'));
        $form->image('avatar', __('Avatar'));
        $form->text('profit', __('Profit'));
        $form->text('losing_profit', __('Losing profit'));
        $form->text('rolling_profit', __('Rolling profit'));
        $form->text('temp_password', __('Temp password'));
        $form->text('bank', __('Bank'));
        $form->text('account', __('Account'));
        $form->text('holder', __('Holder'));
        $form->text('withdrawal_password', __('Withdrawal password'));
        $form->mobile('phone', __('Phone'));
        $form->text('facebook', __('Facebook'));
        $form->switch('activated', __('Activated'));
        $form->text('confirm_code', __('Confirm code'));
        $form->number('deposit_amount', __('Deposit amount'));
        $form->text('admin_yn', __('Admin yn'))->default('N');
        $form->number('account_id', __('Account id'));
        $form->text('telegram_id', __('Telegram id'));
        $form->text('game_token', __('Game token'));
        $form->datetime('game_auth_update', __('Game auth update'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
