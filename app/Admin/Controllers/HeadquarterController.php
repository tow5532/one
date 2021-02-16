<?php

namespace App\Admin\Controllers;

use App\DailyinfoMaster;
use App\Headquarter;
use App\HeadquarterDeposit;
use App\HeadquarterLog;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class HeadquarterController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Headquarter Charge';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Headquarter());

        $grid->model()->orderBy('id', 'desc');

        $grid->disableActions();
        /*$grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
            $actions->disableEdit();
        });*/
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
            $filter->expand();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->column('id', trans('admin.headquarter_charge.no'));
        $grid->user()->username(trans('admin.headquarter_charge.user_id'));

        $grid->column('amount', trans('admin.headquarter_charge.amount'))->display(function ($amount){
            return number_format($amount);
        });
        $grid->column('bonus_percent', trans('admin.headquarter_charge.bonus_amount'))->display(function ($bonus_percent){
            return $bonus_percent . '%';
        });
        $grid->column('bonus_amount', trans('admin.headquarter_charge.bonus_percent'))->display(function ($bonus_amount){
            return number_format($bonus_amount);
        });

        $grid->column(trans('admin.headquarter_charge.sum_amount'))->display(function (){
            return number_format((int)$this->amount + (int)$this->bonus_amount);
        });

        $grid->column(trans('admin.headquarter_charge.income_amount'))->display(function (){
            return number_format(HeadquarterDeposit::where('head_id', $this->id)->sum('deposit_point'));
        });

        $grid->column('created_at', trans('admin.headquarter_charge.created_at'));
        //$grid->column('updated_at', __('Updated at'));

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
        /*$show = new Show(Headquarter::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('amount', __('Amount'));
        $show->field('bonus_percent', __('Bonus percent'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;*/
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        Admin::script("
             $('#amount').keyup(function(){
                var val = $(this).val();
                var bonus = ($('#bonus_percent').val());
                var result = val * bonus / 100;
                $('#bonus_amount').val(result);
            });
            $('#bonus_percent').keyup(function(){
                var val = $(this).val();
                var amount = ($('#amount').val());
                var result = amount * val / 100;
                $('#bonus_amount').val(result);
            });
        ");
        $form = new Form(new Headquarter());

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

        $userTable = config('admin.database.users_table');

        $users = DB::table($userTable)
            ->join('admin_role_users', 'users.id', '=', 'admin_role_users.user_id')
            ->join('admin_roles', 'admin_role_users.role_id', '=', 'admin_roles.id')
            ->where('admin_roles.slug', 'company')
            ->select('users.id', 'users.username')->get();
        $steapArr = array();
        foreach ($users as $user){
            $steapArr[$user->id] = $user->username;
        }
        $form->select('user_id', trans('admin.headquarter_charge.user_id'))->options($steapArr)->rules('required');

        $form->text('amount', trans('admin.headquarter_charge.amount'))->default(0)->rules('required');

        $form->rate('bonus_percent', trans('admin.headquarter_charge.bonus_percent'))->default(0)->rules('required');

        $form->text('bonus_amount', trans('admin.headquarter_charge.bonus_amount'))->readonly()->default(0);

        $form->hidden('sender_id')->value(Admin::user()->id);

        $form->saved(function (Form $form) {

            //잔여 포인트 검색
            $add_cnt        = HeadquarterLog::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
            $minus_cnt      = HeadquarterLog::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
            $in_point       = $add_cnt - $minus_cnt;

            $point = $form->model()->amount + $form->model()->bonus_amount;

            HeadquarterLog::create([
                'user_id' => $form->model()->user_id,
                'head_id' => $form->model()->id,
                'po_content' => 'company_charge',
                'point' => $point,
                'mb_point' => $in_point,
            ]);

            //회원 테이블에 있는 알 수량 업데이트
            $admin = User::find($form->model()->user_id);
            $admin->egg_amount += $point;
            $admin->save();
        });

        return $form;
    }
}
