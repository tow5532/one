<?php

namespace App\Admin\Controllers;

use App\Deposit;
use App\DepositStep;
use App\GameMember;
use App\GameSafeMoney;
use App\GameTourRegist;
use App\LogMoney;
use App\Point;
use App\Refund;
use App\RefundStep;
use App\Tcommand;
use App\TSafer;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class DailyinfoUserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Users';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userTable = config('admin.database.users_table');

        $grid = new Grid(new User());

        $grid->disableCreateButton();
        //$grid->disableFilter();
        //$grid->disableActions();
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
        });
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->model()->join('recommends', $userTable.'.'.  'id', '=', 'recommends.user_id');
        $grid->model()->whereNotNull('recommends.step5_id');

        //어드민 계정이 아닐경우 조건문 을 추가 한다.
        if (!Admin::user()->isRole('administrator') && !Admin::user()->isRole('master')) {

            $recommend = DB::table('recommends')->where('user_id', Admin::user()->id)->first();

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

        //select
        $grid->model()->select('users.*');

        $grid->model()->orderBy('created_at', 'desc');

        $grid->expandFilter();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('username', trans('admin.deposit.user_id_search'));
        });


        $grid->column('username', trans('admin.sales_user.user_id'));

        $grid->column(trans('admin.sales_user.user_nick'))->display(function (){
           $user_nick = GameMember::where('AccountUniqueid', $this->account_id)->value('PlayerName');
           return $user_nick;
        });

        $grid->column(trans('admin.sales_user.total_user_payment'))->display(function (){
            //유저 충전 총액
            $step = DepositStep::where('code', 'success')->first();
            $user_charge_total = Deposit::where('step_id', $step->id)->where('user_id', $this->id)->sum('charge_amount');
            return number_format($user_charge_total);
        });

        $grid->column(trans('admin.sales_user.total_user_exchange'))->display(function (){
            $refund_step = RefundStep::where('code', 'refund_ok')->first();
            $user_refund_total = Refund::where('user_id', $this->id)
                ->where('step_id', $refund_step->id)
                ->sum('amount');
            return number_format($user_refund_total);
        });

        $grid->column(trans('admin.sales_user.total_user_chips'))->display(function (){
            $game_chips = GameMember::where('AccountUniqueid', $this->account_id)
                ->select(DB::raw('sum(convert(bigint,(convert(decimal(38), Have_Money) / 100000000))) as chips'))
                ->get();
            $game_total_chips = 0;
            foreach ($game_chips as $game_chip){
                $game_total_chips += $game_chip->chips;
            }

            //게임머니에서 포인트로 변화할때 신청했지만, 금고에 존재하는 것 조회 및 합산
            $t_safer = TSafer::where('AccountuniqueID', $this->account_id)->where('flag', '0')->sum('safe_money');
            $game_total_chips += $t_safer;

            //토너먼트 예약 머니 총합 조회후 합산
            $tnmt_regist = GameTourRegist::where('AccountUniqueID', $this->account_id)->sum('buyin_money');
            $game_total_chips += $tnmt_regist;

            return number_format($game_total_chips);
        });

        $grid->column('not_in_sun', trans('admin.sales_user.user_sun_point'))->display(function (){
            $userOrigPoint = Point::where(['user_id' => $this->id,'use_point' => '0'])->sum('point');
            $userUsePoint = Point::where(['user_id' => $this->id,'point' => '0'])->sum('use_point');
            $user_point = $userOrigPoint - $userUsePoint;

            //추가로 출금 신청 시 승인 안난것들 조회후 합산
            $refund_step = RefundStep::where('code', 'refund')->first();
            $refund_point = Refund::where('step_id', $refund_step->id)->where('user_id', $this->id)->sum('amount');
            $user_point += $refund_point;

            //게임 머니로 변환 신청 했으나 아직 금고 에 있는 포인트 조회 후 합산
            $t_command_point = Tcommand::where('AccountUniqueID', $this->account_id)->sum('val1');
            $user_point += $t_command_point;

            return number_format($user_point);
        });

        /*$grid->column('not_in_bonus', trans('admin.sales_user.user_bonus_point'))->display(function (){
            //관리자나 무료로 받은 포인트 합계 조회
            $free_cnt = Point::whereIn('po_content', ['join_event', 'admin_charge'])
                ->where('use_point', '0')->where('user_id', $this->id)->sum('point');
            return number_format($free_cnt);
        });*/

        $grid->column('not_in_item', trans('admin.sales_user.buy_item'))->display(function (){
            $game_item_money = 0;
            $logMoneys = LogMoney::where('AccountUniqueID', $this->account_id)->where('Fluctuation_reason', '9')->get();
            foreach ($logMoneys as $logMoney)
            {
                $replace_val = str_replace('-', '', $logMoney->Fluctuation_money);
                $int_val = floor($replace_val);
                $game_item_money += $int_val;
            }
            return number_format($game_item_money);
        });

        /*$grid->column(trans('admin.sales_user.total_user_safe'))->display(function (){
            $safe_amount = GameSafeMoney::where('user_id', $this->id)->sum('safe_money');
            return number_format($safe_amount);
        });*/

        $grid->column('created_at', trans('admin.sales_user.created_at'))->sortable();


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

        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
            });

        $show->field('username', trans('admin.sales_user.user_id'));

        $show->field('account_id',trans('admin.sales_user.user_nick'))->as(function ($account_id){
            $user_nick = GameMember::where('AccountUniqueid', $account_id)->value('PlayerName');
            return $user_nick;
        });

        $show->field('bank', trans('admin.member.bank'));
        $show->field('account', trans('admin.member.account'));
        $show->field('holder', trans('admin.member.holder'));
        $show->field('holder', trans('admin.member.holder'));
        $show->field('withdrawal_password', trans('admin.member.withdrawal'));

        $show->field(trans('admin.sales_user.total_user_payment'))->as(function (){
            //유저 충전 총액
            $step = DepositStep::where('code', 'success')->first();
            $user_charge_total = Deposit::where('step_id', $step->id)->where('user_id', $this->id)->sum('charge_amount');
            return number_format($user_charge_total);
        });

        $show->field(trans('admin.sales_user.total_user_exchange'))->as(function (){
            $refund_step = RefundStep::where('code', 'refund_ok')->first();
            $user_refund_total = Refund::where('user_id', $this->id)
                ->where('step_id', $refund_step->id)
                ->sum('amount');
            return number_format($user_refund_total);
        });

        $show->field(trans('admin.sales_user.user_sun_point'))->as(function (){
            $userOrigPoint = Point::where(['user_id' => $this->id,'use_point' => '0'])->sum('point');
            $userUsePoint = Point::where(['user_id' => $this->id,'point' => '0'])->sum('use_point');
            $user_point = $userOrigPoint - $userUsePoint;

            //추가로 출금 신청 시 승인 안난것들 조회후 합산
            $refund_step = RefundStep::where('code', 'refund')->first();
            $refund_point = Refund::where('step_id', $refund_step->id)->where('user_id', $this->id)->sum('amount');
            $user_point += $refund_point;

            //게임 머니로 변환 신청 했으나 아직 금고 에 있는 포인트 조회 후 합산
            $t_command_point = Tcommand::where('AccountUniqueID', $this->account_id)->sum('val1');
            $user_point += $t_command_point;

            return number_format($user_point);
        });

        $show->field('not_in_bonus', trans('admin.sales_user.user_bonus_point'))->as(function (){
            //관리자나 무료로 받은 포인트 합계 조회
            $free_cnt = Point::whereIn('po_content', ['join_event', 'admin_charge'])
                ->where('use_point', '0')->where('user_id', $this->id)->sum('point');
            return number_format($free_cnt);
        });

        $show->field('not_in_item', trans('admin.sales_user.buy_item'))->as(function (){
            $game_item_money = 0;
            $logMoneys = LogMoney::where('AccountUniqueID', $this->account_id)->where('Fluctuation_reason', '9')->get();
            foreach ($logMoneys as $logMoney)
            {
                $replace_val = str_replace('-', '', $logMoney->Fluctuation_money);
                $int_val = floor($replace_val);
                $game_item_money += $int_val;
            }
            return number_format($game_item_money);
        });

        /*$show->field(trans('admin.sales_user.total_user_safe'))->as(function (){
            $safe_amount = GameSafeMoney::where('user_id', $this->id)->sum('safe_money');
            return number_format($safe_amount);
        });*/

        $show->field(trans('admin.sales_user.total_user_chips'))->as(function (){
            $game_chips = GameMember::where('AccountUniqueid', $this->account_id)
                ->select(DB::raw('sum(convert(bigint,(convert(decimal(38), Have_Money) / 100000000))) as chips'))
                ->get();
            $game_total_chips = 0;
            foreach ($game_chips as $game_chip){
                $game_total_chips += $game_chip->chips;
            }

            //게임머니에서 포인트로 변화할때 신청했지만, 금고에 존재하는 것 조회 및 합산
            $t_safer = TSafer::where('AccountuniqueID', $this->account_id)->where('flag', '0')->sum('safe_money');
            $game_total_chips += $t_safer;

            //토너먼트 예약 머니 총합 조회후 합산
            $tnmt_regist = GameTourRegist::where('AccountUniqueID', $this->account_id)->sum('buyin_money');
            $game_total_chips += $tnmt_regist;

            return number_format($game_total_chips);

        });

        $show->logmoneys('Money Logs', function ($logmoneys){

            $logmoneys->resource('/admin/game-logs');


            $logmoneys->model()->orderBy('idx', 'desc');

            //$logmoneys->idx(trans('admin.game_member.last_login'));
            $logmoneys->Own_Money(trans('admin.game_member.own_amount'))->display(function ($Own_Money){
                return number_format($Own_Money);
            });
            $logmoneys->Fluctuation_money(trans('admin.game_member.chg_amount'))->display(function ($Fluctuation_money){
                return number_format($Fluctuation_money);
            });
            $logmoneys->currently_money(trans('admin.game_member.current_amount'))->display(function ($currently_money){
                return number_format($currently_money);
            });
            $logmoneys->Fluctuation_reason(trans('admin.game_member.chg_reason'))->display(function ($Fluctuation_reason){

                if (app()->getLocale() === 'ko'){
                    $reason_array = array(
                        0 => '알수 없음',
                        1 => '칩 구매',
                        2 => '일반 게임 플레이',
                        3 => '싯앤고 게임 플레이',
                        4 => '토너먼트 게임 플레이',
                        5 => '무료 칩',
                        6 => '구글에서 구매',
                        7 => '쿠폰 사용',
                        8 => '금고로 머니 이동',
                        9 => '칩으로 아이템 구매',
                    );
                } else {
                    $reason_array = array(
                        0 => 'Unknown',
                        1 => 'Buy chips',
                        2 => 'Normal Game Play',
                        3 => 'Sit&Go Game Play',
                        4 => 'tournament Game Play',
                        5 => 'Free chips',
                        6 => 'Buy Google',
                        7 => 'Use Coupon',
                        8 => 'Moving money to the safe',
                        9 => 'Buying items with chips',
                    );
                }
                return $reason_array[$Fluctuation_reason];
            });
            $logmoneys->Fluctuation_date(trans('admin.game_member.chg_date'))->display(function ($Fluctuation_date){
                return $Fluctuation_date;
            });


            $logmoneys->disableCreateButton();
            $logmoneys->disableFilter();
            $logmoneys->disableActions();
            $logmoneys->disableExport();
        });



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

        /*$form->text('username', __('Username'));
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
        $form->text('admin_yn', __('Admin yn'))->default('N');
        $form->number('account_id', __('Account id'));*/

        return $form;
    }
}
