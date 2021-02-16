<?php

namespace App\Admin\Controllers;

use App\Deposit;
use App\DepositStep;
use App\Refund;
use App\RefundStep;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class CurrentSetController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Current Set';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public $sub_cnt = 0;

    protected function grid()
    {
        $step       = (Request::get('step') === null) ? 1 : (int) Request::get('step');
        $sub_step   = Request::get('sub_step');
        $user_id    = Request::get('user_id');

        $userTable = config('admin.database.users_table');
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel());

        //db
        $grid->model()->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id');
        $grid->model()->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id');

        if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master')) {
            if ($step === 1) {
                $grid->model()->whereNotNull('recommends.step1_id');
                $grid->model()->whereNull('recommends.step2_id');
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($step === 2 && $user_id !== null) {
                $grid->model()->where('recommends.step1_id', $user_id);
                $grid->model()->whereNotNull('recommends.step2_id');
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($step === 3 && $user_id !== null) {
                $grid->model()->where('recommends.step2_id', $user_id);
                $grid->model()->whereNotNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($step === 4 && $user_id !== null) {
                $grid->model()->where('recommends.step3_id', $user_id);
                $grid->model()->whereNotNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($step === 5 && $user_id !== null) {
                $grid->model()->where('recommends.step4_id', $user_id);
                $grid->model()->whereNotNull('recommends.step5_id');
            }
        } else {
            //권한 등급 조회
            if ($user_id === null) {
                //로그인한 회원 권한 조회
                $admin_role         = Admin::user()->roles;
                $admin_role_id      = $admin_role[0]['id'];
            } else {
                $user = DB::table('admin_role_users')->where('user_id', $user_id)->first();
                $admin_role_id = DB::table('admin_roles')->where('id', $user->role_id)->value('id');
            }
            $admin_order = DB::table('admin_roles_order')->where('roles_id', $admin_role_id)->first();

            //권한등급 숫자로 조회할 컬럼명 선언
            $order_num  = $admin_order->orderby -1;
            $low_num    = $admin_order->orderby;

            $admin_step_col = 'step'.$order_num .'_id';
            $low_step_col   = 'step'.$low_num .'_id';

            //권한 자 계정 시퀀스
            $admin_id = $user_id ?? Admin::user()->id;

            if ((int)$order_num === 1) {
                $grid->model()->where('recommends.' . $admin_step_col, $admin_id);
                $grid->model()->whereNotNull($low_step_col);
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ((int)$order_num === 2) {
                $grid->model()->where('recommends.' . $admin_step_col, $admin_id);
                $grid->model()->whereNotNull($low_step_col);
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ((int)$order_num === 3) {
                $grid->model()->where('recommends.' . $admin_step_col, $admin_id);
                $grid->model()->whereNotNull($low_step_col);
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ((int)$order_num === 4) {
                $grid->model()->where('recommends.' . $admin_step_col, $admin_id);
                $grid->model()->whereNotNull($low_step_col);
            }
        }



        $grid->disableCreateButton();
        $grid->disableActions();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('username', trans('admin.member.user_id'));
            $options = [
            ];
            //$filter->between('created_at', '등록일')->datetime($options);
            $filter->expand();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });



        $grid->column('id', 'No');

        $grid->column('roles', trans('admin.member.level'))->pluck('name')->label();

        $grid->column('username', trans('admin.member.user_id'));

        if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master') || Admin::user()->isRole('company')) {
            $grid->column('총판매 금액');
            $grid->column('보유칩 합계')->display(function () {
                $today = Carbon::today();
                $todayDate = $today->toDateString();
                $plucked = $this->roles->pluck('slug');
                $role_slug = $plucked[0];
            });
            $grid->column('칩 구입');
            $grid->column('재판매');
        }

        $grid->column('유저충전금액')->display(function (){
            $today              = Carbon::today();
            $todayDate          = $today->toDateString();
            $plucked            = $this->roles->pluck('slug');
            $role_slug          = $plucked[0];
            $user_charge_total  = 0;

            if ($role_slug === 'company'){
                //유저가해당일에 충전한 액수
                $step = DepositStep::where('code', 'success')->first();
                $user_charge_total = Deposit::whereDate('created_at', '=', $todayDate)
                    ->where('step_id', $step->id)
                    ->sum('charge_amount');
            }
            elseif ($role_slug === 'sub_company' || $role_slug === 'distributor' || $role_slug === 'store'){
                if ($role_slug === 'sub_company'){
                    $where_query = 'recommends.step2_id';
                    $not_query = 'recommends.step3_id';
                }
                elseif ($role_slug === 'distributor'){
                    $where_query = 'recommends.step3_id';
                    $not_query = 'recommends.step4_id';
                }
                elseif ($role_slug === 'store'){
                    $where_query = 'recommends.step4_id';
                    $not_query = 'recommends.step5_id';
                }

                //해당 하부 회원들 조회
                $lows = DB::table('users')
                    ->join('recommends', 'users.id', '=', 'recommends.user_id')
                    ->where($where_query, '=', $this->id)
                    ->whereNotNull($not_query)
                    ->select('users.*')->get();
                $userArray = array();
                foreach ($lows as $low) {
                    array_push($userArray, $low->id);
                }
                $gameArray = array();
                foreach ($lows as $low) {
                    array_push($gameArray, $low->account_id);
                }

                //유저 충전 총액
                $step = DepositStep::where('code', 'success')->first();
                $user_charge_total = Deposit::whereDate('created_at', '=', $todayDate)
                    ->where('step_id', $step->id)
                    ->whereIn('user_id', $userArray)
                    ->sum('charge_amount');
            }

            return $user_charge_total;
        });

        $grid->column('유저환전금액')->display(function (){
            $today              = Carbon::today();
            $todayDate          = $today->toDateString();
            $plucked            = $this->roles->pluck('slug');
            $role_slug          = $plucked[0];
            $user_refund_total  = 0;

            if ($role_slug === 'company'){
                //유저가해당일에 환전신청완료 액수
                $refund_step = RefundStep::where('code', 'refund_ok')->first();
                $user_refund_total = Refund::whereDate('created_at', '=', $todayDate)
                    ->where('step_id', $refund_step->id)
                    ->sum('amount');
            }
            elseif ($role_slug === 'sub_company' || $role_slug === 'distributor' || $role_slug === 'store'){
                if ($role_slug === 'sub_company'){
                    $where_query = 'recommends.step2_id';
                    $not_query = 'recommends.step3_id';
                }
                elseif ($role_slug === 'distributor'){
                    $where_query = 'recommends.step3_id';
                    $not_query = 'recommends.step4_id';
                }
                elseif ($role_slug === 'store'){
                    $where_query = 'recommends.step4_id';
                    $not_query = 'recommends.step5_id';
                }

                //해당 하부 회원들 조회
                $lows = DB::table('users')
                    ->join('recommends', 'users.id', '=', 'recommends.user_id')
                    ->where($where_query, '=', $this->id)
                    ->whereNotNull($not_query)
                    ->select('users.*')->get();
                $userArray = array();
                foreach ($lows as $low) {
                    array_push($userArray, $low->id);
                }
                $gameArray = array();
                foreach ($lows as $low) {
                    array_push($gameArray, $low->account_id);
                }

                $refund_step = RefundStep::where('code', 'refund_ok')->first();
                $user_refund_total = Refund::whereDate('created_at', '=', $todayDate)
                    ->whereIn('user_id', $userArray)
                    ->where('step_id', $refund_step->id)
                    ->sum('amount');
            }

            return $user_refund_total;
        });

        $grid->column('수익')->display(function (){
            $today          = Carbon::today();
            $todayDate      = $today->toDateString();
            $plucked        = $this->roles->pluck('slug');
            $role_slug      = $plucked[0];
            $profit         = $this->profit;
            $saleFee        = 0.23;
            $rev            = 0;


            if ($role_slug === 'company'){
                $step = DepositStep::where('code', 'success')->first();
                $user_charge_total = Deposit::whereDate('created_at', '=', $todayDate)->where('step_id', $step->id)->sum('charge_amount');

                //수수료를 백분율로 환산
                $fee_back = $profit / 100;
                $rev = ($user_charge_total * $saleFee) * $fee_back;
            }
            elseif ($role_slug === 'sub_company' || $role_slug === 'distributor' || $role_slug === 'store'){
                if ($role_slug === 'sub_company'){
                    $where_query = 'recommends.step2_id';
                    $not_query = 'recommends.step3_id';
                }
                elseif ($role_slug === 'distributor'){
                    $where_query = 'recommends.step3_id';
                    $not_query = 'recommends.step4_id';
                }
                elseif ($role_slug === 'store'){
                    $where_query = 'recommends.step4_id';
                    $not_query = 'recommends.step5_id';
                }

                //해당 하부 회원들 조회
                $lows = DB::table('users')
                    ->join('recommends', 'users.id', '=', 'recommends.user_id')
                    ->where($where_query, '=', $this->id)
                    ->whereNotNull($not_query)
                    ->select('users.*')->get();
                $userArray = array();
                foreach ($lows as $low) {
                    array_push($userArray, $low->id);
                }
                $gameArray = array();
                foreach ($lows as $low) {
                    array_push($gameArray, $low->account_id);
                }

                //유저 충전 총액
                $step = DepositStep::where('code', 'success')->first();
                $user_charge_total = Deposit::whereDate('created_at', '=', $todayDate)
                    ->where('step_id', $step->id)
                    ->whereIn('user_id', $userArray)
                    ->sum('charge_amount');
                //수수료를 백분율로 환산
                $fee_back = $profit / 100;
                $rev = ($user_charge_total * $saleFee) * $fee_back;
            }

            return number_format($rev);
        });



        if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master')) {
            if ($step < 5) {
                $grid->column(trans('admin.member.lower_view'))->display(function () {
                    $step = (Request::get('step') === null) ? 1 : (int)Request::get('step');
                    $next_step = $step + 1;
                    return  '<a href="current-set?step=' . $next_step . '&user_id=' . $this->getKey() . '">Link</a>';
                });
            }
        } else {
            if ($order_num < 4) {
                $grid->column(trans('admin.member.lower_view'))->display(function () {
                    $step = (Request::get('step') === null) ? 1 : (int)Request::get('step');
                    $next_step = $step + 1;
                    if ($this->sub_cnt === 0){
                        return '';
                    } else {
                        return '<a href="current-set?step=' . $next_step . '&user_id=' . $this->getKey() . '">Link</a>';
                    }
                });
            }
        }

        $grid->column('Higher View')->display(function (){
           // return '<a href="subs?step=' . $next_step . '&user_id=' . $this->getKey() . '">Link</a>';
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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        $show->field('email_verified_at', __('Email verified at'));
        $show->field('password', __('Password'));
        $show->field('remember_token', __('Remember token'));
        $show->field('avatar', __('Avatar'));
        $show->field('profit', __('Profit'));
        $show->field('temp_password', __('Temp password'));
        $show->field('bank', __('Bank'));
        $show->field('account', __('Account'));
        $show->field('holder', __('Holder'));
        $show->field('withdrawal_password', __('Withdrawal password'));
        $show->field('phone', __('Phone'));
        $show->field('facebook', __('Facebook'));
        $show->field('activated', __('Activated'));
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
        $form = new Form(new User());

        $form->text('username', __('Username'));
        $form->text('name', __('Name'));
        $form->email('email', __('Email'));
        $form->datetime('email_verified_at', __('Email verified at'))->default(date('Y-m-d H:i:s'));
        $form->password('password', __('Password'));
        $form->text('remember_token', __('Remember token'));
        $form->image('avatar', __('Avatar'));
        $form->text('profit', __('Profit'));
        $form->text('temp_password', __('Temp password'));
        $form->text('bank', __('Bank'));
        $form->text('account', __('Account'));
        $form->text('holder', __('Holder'));
        $form->text('withdrawal_password', __('Withdrawal password'));
        $form->mobile('phone', __('Phone'));
        $form->text('facebook', __('Facebook'));
        $form->switch('activated', __('Activated'));

        return $form;
    }
}
