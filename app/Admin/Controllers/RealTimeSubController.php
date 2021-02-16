<?php

namespace App\Admin\Controllers;

use App\DailyinfoCompany;
use App\Deposit;
use App\DepositStep;
use App\GameInfo;
use App\GameMember;
use App\GameSafeMoney;
use App\HeadquarterLog;
use App\HouseEdge;
use App\Point;
use App\Recommend;
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

class RealTimeSubController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'RealTime Company';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userTable = config('admin.database.users_table');
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel());

        $grid->disableCreateButton();
        //$grid->disableFilter();
        $grid->disableActions();
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        //param
        $url_seg    = request()->segment(2);
        $seqArray   = explode('-', $url_seg);
        $level      = $seqArray[1];

        //model
        $grid->model()->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id');
        $grid->model()->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id');

        if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master') || !Admin::user()->isRole('company')) {
            if ($level === 'sub_company') {
                $grid->model()->whereNotNull('recommends.step1_id');
                $grid->model()->whereNotNull('recommends.step2_id');
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($level === 'distributor') {
                $grid->model()->whereNotNull('recommends.step1_id');
                $grid->model()->whereNotNull('recommends.step2_id');
                $grid->model()->whereNotNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($level === 'store') {
                $grid->model()->whereNotNull('recommends.step1_id');
                $grid->model()->whereNotNull('recommends.step2_id');
                $grid->model()->whereNotNull('recommends.step3_id');
                $grid->model()->whereNotNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
        }
        else {
            if ($level === 'sub_company') {
                $grid->model()->where('recommends.step2_id', '=', Admin::user()->id);
                $grid->model()->whereNotNull('recommends.step2_id');
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($level === 'distributor' || $level === 'store'){
                //로그인한 회원 권한 조회
                $recommend = Recommend::where('user_id', Admin::user()->id)->first();

                if ($recommend->step1_id === Admin::user()->id) {
                    $step_col = 'step1_id';
                }
                if ($recommend->step2_id === Admin::user()->id) {
                    $step_col = 'step2_id';
                }
                if ($recommend->step3_id === Admin::user()->id) {
                    $step_col = 'step3_id';
                }
                if ($recommend->step4_id === Admin::user()->id) {
                    $step_col = 'step4_id';
                }
                if ($recommend->step5_id === Admin::user()->id) {
                    $step_col = 'step5_id';
                }
                $grid->model()->where('recommends.' . $step_col, Admin::user()->id);
            }
        }



        //#############################################

        $grid->column('id', 'No');

        $grid->column('시간')->display(function (){
           return Carbon::now()->format('Y-m-d H:i:s');
        });

        $grid->column('roles', trans('admin.member.level'))->pluck('name')->label();

        $grid->column('username', trans('admin.member.user_id'));

        $grid->column('유저충전금액')->display(function (){

            $plucked = $this->roles->pluck('slug');
            $role_slug = $plucked[0];

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

            //부본 계정의 시퀀스 아이디 조회
            $sub_id     = $this->id;
            $sub_name   = $this->username;
            $fee        = $this->profit;

            //해당 하부 회원들 조회
            $lows = DB::table('users')
                ->join('recommends', 'users.id', '=', 'recommends.user_id')
                ->where($where_query, '=', $sub_id)
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
            $user_charge_total = Deposit::whereDate('created_at', '=', Carbon::today()->toDateString())
                ->where('step_id', $step->id)
                ->whereIn('user_id', $userArray)
                ->sum('charge_amount');

            return number_format($user_charge_total);
        });

        $grid->column('유저 환전 금액')->display(function (){
            $plucked = $this->roles->pluck('slug');
            $role_slug = $plucked[0];

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

            //부본 계정의 시퀀스 아이디 조회
            $sub_id     = $this->id;
            $sub_name   = $this->username;
            $fee        = $this->profit;

            //해당 하부 회원들 조회
            $lows = DB::table('users')
                ->join('recommends', 'users.id', '=', 'recommends.user_id')
                ->where($where_query, '=', $sub_id)
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

            //유저 환전 총액
            $refund_step = RefundStep::where('code', 'refund_ok')->first();
            $user_refund_total = Refund::whereDate('created_at', '=', Carbon::today()->toDateString())
                ->whereIn('user_id', $userArray)
                ->where('step_id', $refund_step->id)
                ->sum('amount');
            return number_format($user_refund_total);
        });

        $grid->column('유저 보유 칩')->display(function (){
            $plucked = $this->roles->pluck('slug');
            $role_slug = $plucked[0];

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

            //부본 계정의 시퀀스 아이디 조회
            $sub_id     = $this->id;
            $sub_name   = $this->username;
            $fee        = $this->profit;

            //해당 하부 회원들 조회
            $lows = DB::table('users')
                ->join('recommends', 'users.id', '=', 'recommends.user_id')
                ->where($where_query, '=', $sub_id)
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

            $game_chips = GameMember::whereIn('AccountUniqueid', $gameArray)
                ->select(DB::raw('sum(convert(bigint,(convert(decimal(38), Have_Money) / 100000000))) as chips'))
                ->get();
            $game_total_chips = 0;
            foreach ($game_chips as $game_chip){
                $game_total_chips = $game_chip->chips / 100 ?? 0;
            }
            return $game_total_chips;
        });

        $grid->column('유저 금고')->display(function (){
            $plucked = $this->roles->pluck('slug');
            $role_slug = $plucked[0];

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

            //부본 계정의 시퀀스 아이디 조회
            $sub_id     = $this->id;
            $sub_name   = $this->username;
            $fee        = $this->profit;

            //해당 하부 회원들 조회
            $lows = DB::table('users')
                ->join('recommends', 'users.id', '=', 'recommends.user_id')
                ->where($where_query, '=', $sub_id)
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

            //유저 금고 총합
            $safe_amount = GameSafeMoney::whereIn('user_id', $userArray)->sum('safe_money');
            return $safe_amount;
        });

        $grid->column('유저 디파짓')->display(function (){
            $plucked = $this->roles->pluck('slug');
            $role_slug = $plucked[0];

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

            //부본 계정의 시퀀스 아이디 조회
            $sub_id     = $this->id;
            $sub_name   = $this->username;
            $fee        = $this->profit;

            //해당 하부 회원들 조회
            $lows = DB::table('users')
                ->join('recommends', 'users.id', '=', 'recommends.user_id')
                ->where($where_query, '=', $sub_id)
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

            //유저 디파짓 총합
            $add_cnt = Point::whereIn('user_id', $userArray)->where('use_point', '=', '0')->sum('point');
            $minus_cnt = Point::whereIn('user_id', $userArray)->where('point', '=', '0')->sum('use_point');
            $user_point   = $add_cnt - $minus_cnt;

            return number_format($user_point);
        });

        $grid->column('수익')->display(function (){
            $plucked = $this->roles->pluck('slug');
            $role_slug = $plucked[0];

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

            //부본 계정의 시퀀스 아이디 조회
            $sub_id     = $this->id;
            $sub_name   = $this->username;
            $fee        = $this->profit;
            $saleFee    =  0.23;

            //해당 하부 회원들 조회
            $lows = DB::table('users')
                ->join('recommends', 'users.id', '=', 'recommends.user_id')
                ->where($where_query, '=', $sub_id)
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
            $user_charge_total = Deposit::whereDate('created_at', '=', Carbon::today()->toDateString())
                ->where('step_id', $step->id)
                ->whereIn('user_id', $userArray)
                ->sum('charge_amount');

            //수수료를 백분율로 환산
            $fee_back = $fee / 100;
            $rev = ($user_charge_total * $saleFee) * $fee_back;

            return $rev;
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
        /*$show = new Show(DailyinfoCompany::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('search_date', __('Search date'));
        $show->field('total_payment', __('Total payment'));
        $show->field('total_refund', __('Total refund'));
        $show->field('company_chip_payment', __('Company chip payment'));
        $show->field('company_chip_reload', __('Company chip reload'));
        $show->field('company_chip_total', __('Company chip total'));
        $show->field('user_chips', __('User chips'));
        $show->field('user_safe', __('User safe'));
        $show->field('user_deposit', __('User deposit'));
        $show->field('normal_company_fee', __('Normal company fee'));
        $show->field('tour_company_fee', __('Tour company fee'));
        $show->field('sit_company_fee', __('Sit company fee'));
        $show->field('company_rev', __('Company rev'));
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
        /*$form = new Form(new DailyinfoCompany());

        $form->date('search_date', __('Search date'))->default(date('Y-m-d'));
        $form->text('total_payment', __('Total payment'));
        $form->text('total_refund', __('Total refund'));
        $form->text('company_chip_payment', __('Company chip payment'));
        $form->text('company_chip_reload', __('Company chip reload'));
        $form->text('company_chip_total', __('Company chip total'));
        $form->text('user_chips', __('User chips'));
        $form->text('user_safe', __('User safe'));
        $form->text('user_deposit', __('User deposit'));
        $form->text('normal_company_fee', __('Normal company fee'));
        $form->text('tour_company_fee', __('Tour company fee'));
        $form->text('sit_company_fee', __('Sit company fee'));
        $form->text('company_rev', __('Company rev'));

        return $form;*/
    }
}
