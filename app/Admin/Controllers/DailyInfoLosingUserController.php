<?php

namespace App\Admin\Controllers;

use App\Deposit;
use App\DepositStep;
use App\Point;
use App\Recommend;
use App\Refund;
use App\RefundStep;
use App\SlotGameAuth;
use App\SlotGameMoneyIn;
use App\SlotGameMoneyOut;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class DailyInfoLosingUserController extends AdminController
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


        $userTable = config('admin.database.users_table');
        $grid->model()->join('recommends', $userTable.'.'.  'id', '=', 'recommends.user_id');
        $grid->model()->whereNotNull('recommends.step5_id');

        //어드민 계정이 아닐경우 조건문 을 추가 한다.
        if (!Admin::user()->isRole('administrator') && !Admin::user()->isRole('master')) {

            $recommend = DB::table('recommends')->where('user_id', Admin::user()->id)->first();
            $step_col = '';

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




        $grid->column('username', trans('admin.sales_user.user_id'));

        $grid->column('account_id', trans('admin.sales_user.user_nick'));

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


        $grid->column('not_in_sun', '회원보유포인트')->display(function (){
            $userOrigPoint = Point::where(['user_id' => $this->id,'use_point' => '0'])->sum('point');
            $userUsePoint = Point::where(['user_id' => $this->id,'point' => '0'])->sum('use_point');
            $user_point = $userOrigPoint - $userUsePoint;

            //추가로 출금 신청 시 승인 안난것들 조회후 합산
            $refund_step = RefundStep::where('code', 'refund')->first();
            $refund_point = Refund::where('step_id', $refund_step->id)->where('user_id', $this->id)->sum('amount');
            $user_point += $refund_point;

            //게임 머니로 변환 신청 했으나 아직 금고 에 있는 포인트 조회 후 합산
            //$slotMoneyIn_amount = SlotGameMoneyIn::where('Aid', $this->account_id)->where('flag', '0')->sum('Val1');

            //$user_point += $slotMoneyIn_amount;

            return number_format($user_point);
        });

        /*$grid->column('보유게임머니')->display(function (){
            $game = SlotGameAuth::where('Aid', $this->account_id)->first();
            $game_money = ($game === null) ? 0 : $game->Chip;

            //일단 게임머니 -> 포인트로 이동하기 위해 금고에 있는 것도 포인트로  합산
            $slotMOneyOut_amount = SlotGameMoneyOut::where('Aid', $this->account_id)->where('flag', '0')->sum('SaveMoney');

            $game_money += $slotMOneyOut_amount;

            return number_format($game_money);
        });*/

        /*$grid->column('not_in_bonus', trans('admin.sales_user.user_bonus_point'))->display(function (){
            //관리자나 무료로 받은 포인트 합계 조회
            $free_cnt = Point::whereIn('po_content', ['join_event', 'admin_charge'])
                ->where('use_point', '0')->where('user_id', $this->id)->sum('point');
            return number_format($free_cnt);
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

        //조회 가능한 회원인지 확인
        if (!Admin::user()->inRoles(['administrator', 'master'])){

            //자기 밑에 있는지 확인
            $recommend  = Recommend::where('user_id', Admin::user()->id)
                ->orWhere('step1_id', Admin::user()->id)
                ->orWhere('step2_id', Admin::user()->id)
                ->orWhere('step3_id', Admin::user()->id)
                ->orWhere('step4_id', Admin::user()->id)
                ->first();

            if ($recommend->count() === 0){
                $error = new MessageBag([
                    'title'   => '잘못된 접근 입니다.',
                    'message' => '해당 주소로 접근 하실수 없습니다.',
                ]);
                return back()->with(compact('error'));
            }


        }

        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableDelete();
        });

        $show->field('username', trans('admin.sales_user.user_id'));

        $show->field('account_id', trans('admin.sales_user.user_nick'));

        $show->field('bank', trans('admin.member.bank'));
        $show->field('account', trans('admin.member.account'));
        $show->field('holder', trans('admin.member.holder'));
        $show->field('holder', trans('admin.member.holder'));

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

        $show->field('not_in_sun', '회원보유포인트')->as(function (){
            $userOrigPoint = Point::where(['user_id' => $this->id,'use_point' => '0'])->sum('point');
            $userUsePoint = Point::where(['user_id' => $this->id,'point' => '0'])->sum('use_point');
            $user_point = $userOrigPoint - $userUsePoint;

            //추가로 출금 신청 시 승인 안난것들 조회후 합산
            $refund_step = RefundStep::where('code', 'refund')->first();
            $refund_point = Refund::where('step_id', $refund_step->id)->where('user_id', $this->id)->sum('amount');
            $user_point += $refund_point;

            //게임 머니로 변환 신청 했으나 아직 금고 에 있는 포인트 조회 후 합산
            //$slotMoneyIn_amount = SlotGameMoneyIn::where('Aid', $this->account_id)->where('flag', '0')->sum('Val1');

            //일단 게임머니 -> 포인트로 이동하기 위해 금고에 있는 것도 포인트로  합산
            //$slotMOneyOut_amount = SlotGameMoneyOut::where('Aid', $this->account_id)->where('flag', '0')->sum('SaveMoney');
            $slotMoneyIn_amount = 0;
            $slotMOneyOut_amount = 0;

            $user_point += $slotMoneyIn_amount;
            $user_point += $slotMOneyOut_amount;

            return number_format($user_point);
        });

        /*$show->field('not_in_bonus', trans('admin.sales_user.user_bonus_point'))->as(function (){
            //관리자나 무료로 받은 포인트 합계 조회
            $free_cnt = Point::whereIn('po_content', ['join_event', 'admin_charge'])
                ->where('use_point', '0')->where('user_id', $this->id)->sum('point');
            return number_format($free_cnt);
        });*/

        $show->points('포인트 내역', function ($points){
            $points->resource('/points');
            $points->model()->orderBy('id', 'desc');

            $points->column('po_content', '구분')->using([
                'charge' => '입금신청',
                'send_game' => '게임머니로 교환',
                'send_web' => '포인트로 교환',
                'withdraw' => '출금신청',
                'join_event' => '회원가입이벤트',
                'admin_charge' => '매장입금',
            ])->sortable();

            $points->column('not_point', '금액')->display(function (){
                return ((int)$this->point > 0 ) ?  number_format($this->point) : number_format($this->use_point);
            });

            $points->column('created_at', '등록일');


            $points->disableCreateButton();
            //$grid->disableFilter();
            //$grid->disableActions();
            $points->filter(function ($filter){
                $filter->disableIdFilter();
            });
            $points->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
            });
            $points->batchActions(function ($batchActions){
                $batchActions->disableDelete();
            });

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

    }
}
