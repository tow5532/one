<?php

namespace App\Admin\Controllers;

use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class DailyInfoSalesMixController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '정산관리';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $in_arr= array('company', 'sub_company', 'distributor', 'store');
        $slug_param = request()->slug;

        if (!in_array($slug_param, $in_arr, true)){
            $error = new MessageBag([
                'title'   => '잘못된 접근 입니다.',
                'message' => '해당 주소로 접근 하실수 없습니다.',
            ]);
            return back()->with(compact('error'));
        }

        $grid = new Grid(new User());
        $userTable = config('admin.database.users_table');


        $grid->model()->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id');
        $grid->model()->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id');


        if (Admin::user()->inRoles(['administrator', 'master'])){
            if ($slug_param === 'company') {
                $grid->model()->whereNotNull('recommends.step1_id');
                $grid->model()->whereNull('recommends.step2_id');
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($slug_param === 'sub_company') {
                $grid->model()->whereNotNull('recommends.step2_id');
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($slug_param === 'distributor') {
                $grid->model()->whereNotNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($slug_param === 'store') {
                $grid->model()->whereNotNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
        }
        else {

        }




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
        $show->field('losing_cnt', __('Losing cnt'));
        $show->field('rolling_cnt', __('Rolling cnt'));

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
        $form->text('losing_cnt', __('Losing cnt'));
        $form->text('rolling_cnt', __('Rolling cnt'));

        return $form;
    }
}
