<?php

namespace App\Admin\Controllers;

use App\Deposit;
use App\DepositStep;
use App\HeadquarterLog;
use App\Point;
use App\Refund;
use App\RefundStep;
use App\SlotGameMoneyIn;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class PointPublicController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '매장입출금관리';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Point());

        if (! Admin::user()->inRoles(['administrator', 'master'])){
            $userTable = 'users';
            $users = DB::table('users')
                ->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id')
                ->where('recommends.step4_id', Admin::user()->id)
                ->whereNotNull('recommends.step5_id')
                ->where('users.is_store_user', 'Y')
                ->select($userTable . '.' . 'id')->get();
            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->id);
            }
            $grid->model()->whereIn('user_id', $userArray);
        }

        $grid->model()->orderBy('id', 'desc');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('user.username', '회원 아이디');
            //카테고리- 입금 :charge, 게임머니로변환:send_game, 게임머리에서포인트로변환:send_web, 포인트환전:refund, 회원가입:join, 관리자:admin
            $steapArr = [
                'charge' => '입금',
                'send_game' => '게임머니로변환',
                'send_web' => '포인트로변환',
                'withdraw' => '포인트환전',
                'join_event' => '회원가입',
                'admin_charge' => '관리자 충전',
                'admin_withdraw' => '관리자 출금'
            ];
            $filter->equal('po_content', '구분')->select($steapArr);
            $filter->between('created_at', '등록일')->date();
            $filter->expand();
        });


        $grid->column('id', '번호');

        $grid->user()->username(trans('admin.deposit.userid'));

        $grid->column('po_content', '구분')->using([
            'charge' => '입금',
            'send_game' => '게임머니로변환',
            'send_web' => '포인트로변환',
            'withdraw' => '포인트환전',
            'join_event' => '회원가입',
            'admin_charge' => '충전',
            'admin_withdraw' => '출금'
        ])->sortable();


        $grid->column('mb_point', '이전 포인트')->display(function ($mb_point){
            return number_format($mb_point);
        });

        $grid->column('point', '수량')->display(function ($point){
            //return number_format($point);
            return ((int)$this->use_point === 0) ? number_format($point) :  ' - ' . number_format($this->use_point);
        });

        $grid->column('no_col', '합계')->display(function (){
            //return ((int)$this->use_point === 0) ? number_format($this->mb_point + $this->point) :  number_format($this->mb_point - $this->use_point);
            return number_format($this->mo_point);
        });

        /*$grid->column('use_point', 'Use point')->display(function ($use_point){
            return number_format($use_point);
        });*/

        /*$grid->column('not_point', '수량')->dislay(function (){
            return 'ㄴㅁㅇㄹㅇㅁㄴㄹ'. ((int)$this->point > 0 ) ?  number_format($this->point) : number_format($this->use_point);
        });*/


        $grid->column('created_at', '등록일');
        //$grid->column('updated_at', 'Updated at');


        /*$grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });*/


        $grid->disableActions();
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
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
        /*$show = new Show(Point::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('deposit_id', __('Deposit id'));
        $show->field('refund_id', __('Refund id'));
        $show->field('po_content', __('Po content'));
        $show->field('point', __('Point'));
        $show->field('use_point', __('Use point'));
        $show->field('mb_point', __('Mb point'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('game_id', __('Game id'));

        return $show;*/
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Point());


        //잔여 포인트 검색
        $add_cnt        = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
        $minus_cnt      = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
        $in_point       = $add_cnt - $minus_cnt;
        $form->display('현재남은 알 수량')->value(number_format($in_point));

        $userTable = config('admin.database.users_table');
        $users = DB::table('users')
            ->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id')
            ->where('recommends.step4_id', Admin::user()->id)
            ->whereNotNull('recommends.step1_id')
            ->whereNotNull('recommends.step2_id')
            ->whereNotNull('recommends.step3_id')
            ->whereNotNull('recommends.step5_id')
            ->where('users.is_store_user', 'Y')
            ->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id')->get();
        $userArr = array();
        foreach ($users as $user){
            $userArr[$user->id] = $user->username;
        }
        $form->select('user_id', trans('admin.headquarter_charge.user_id'))->options($userArr)->rules('required');

        $form->select('po_content', '방법')->options([
            'admin_charge' => '입금',
            'admin_withdraw' => '출금'
        ])->rules('required');

        $form->number('point', '수량')->rules('required');

        $form->hidden('use_point')->value('0');


        $form->hidden('mb_point');


        $form->saving(function (Form $form) {
            if ($form->po_content === 'admin_charge'){
                //입금 처리시, 해당 계정 알 수량 확인
                $add_cnt    = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
                $minus_cnt  = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
                $in_point   = $add_cnt - $minus_cnt;

                if ((int)$in_point < (int)$form->point){
                    $error = new MessageBag([
                        'title' => '사용할수 있는 알 수량이 적습니다.',
                        'message' => '알 충전을 해야 입금 신청을 완료 할 수 있습니다.',
                    ]);
                    return back()->with(compact('error'));
                }
            }

            //입금
            if ($form->po_content === 'admin_withdraw'){
                $form->use_point    = $form->point;
                $form->point        = '0';
            }

            //출금시에 해당 포인트가 가능하게 있는지 확인
            if ($form->po_content === 'admin_withdraw'){
                $is_add_cnt = Point::where('user_id', $form->user_id)->where('use_point', '=', '0')->sum('point');
                $is_minus_cnt = Point::where('user_id', $form->user_id)->where('point', '=', '0')->sum('use_point');
                $sum_cnt = $is_add_cnt - $is_minus_cnt;

                if ((int)$form->use_point > $sum_cnt){
                    $error = new MessageBag([
                        'title' => '사용할수 있는 회원 포인트 수량이 적습니다.',
                        'message' => '포인트 수량을 확인해 주세요.',
                    ]);
                    return back()->with(compact('error'));
                }
            }

            //이전 포인트 조회
            $add_cnt        = Point::where('user_id', $form->user_id)->where('use_point', '=', '0')->sum('point');
            $minus_cnt      = Point::where('user_id', $form->user_id)->where('point', '=', '0')->sum('use_point');
            $user_point     = $add_cnt - $minus_cnt;

            $form->mb_point = $user_point;
        });

        $form->saved(function (Form $form) {
            //잔여 알 검색
            $add_cnt    = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
            $minus_cnt  = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
            $in_point   = $add_cnt - $minus_cnt;

            //충전시 알차감, 출금시 알 더해줌
            if ($form->model()->po_content === 'admin_charge'){
                HeadquarterLog::create([
                    'user_id' => Admin::user()->id,
                    'head_id' => 0,
                    'po_content' => 'user_charge',
                    'use_point' => $form->model()->point,
                    'point' => '0',
                    'mb_point' => $in_point,
                ]);
                $user = User::find(Admin::user()->id);
                $user->egg_amount -= $form->model()->point;
                $user->save();

                //정산읠 위해서 입금 된것 처럼 해준다.
                $step   = DepositStep::where('code', 'success')->first();
                $deposit = new Deposit;
                $deposit->user_id = $form->model()->user_id;
                $deposit->admin_id = Admin::user()->id;
                $deposit->step_id = $step->id;
                $deposit->amount = $form->model()->point;
                $deposit->charge_amount = $form->model()->point;
                $deposit->quote = '1';
                $deposit->holder = 'admin';
                $deposit->header_info = request()->server('HTTP_USER_AGENT');
                $deposit->ip = request()->ip();
                $deposit->save();

                //게임 DB에 바로 해당 게임머니를 이동 시켜 준다.
                //게임 아이디 존재 유무
                $game_user = User::find($form->model()->user_id);
                if ($game_user->account_id) {
                    $game_safe = new SlotGameMoneyIn;
                    $game_safe->Aid = $game_user->account_id;
                    $game_safe->Command = 'money change';
                    $game_safe->Val1 = $form->model()->point;
                    $game_safe->Comment = 'transfer';
                    $game_safe->flag = '0';
                    $game_safe->save();

                    //포인트 내역에 다시 게임머니 이동 내역 등록 해준다.
                    //이전 포인트 조회
                    $add_cnt        = Point::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
                    $minus_cnt      = Point::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
                    $user_point     = $add_cnt - $minus_cnt;

                    $point = new Point;
                    $point->user_id = $form->model()->user_id;
                    $point->po_content = 'send_game';
                    $point->point = '0';
                    $point->use_point = $form->model()->point;
                    $point->mb_point = $user_point;
                    $point->game_id = 1;
                    $point->save();
                }

            }
            elseif ($form->model()->po_content === 'admin_withdraw'){
                HeadquarterLog::create([
                    'user_id' => Admin::user()->id,
                    'head_id' => 0,
                    'po_content' => 'user_withdraw',
                    'point' => $form->model()->use_point,
                    'use_point' => '0',
                    'mb_point' => $in_point,
                ]);

                $user = User::find(Admin::user()->id);
                $user->egg_amount += $form->model()->use_point;
                $user->save();

                //정산읠 위해서 출금 된것 처럼 해준다.
                $step = RefundStep::where('code', 'refund_ok')->first();

                $refund = new Refund;
                $refund->user_id = $form->model()->user_id;
                $refund->admin_id = Admin::user()->id;
                $refund->step_id = $step->id;
                $refund->amount = $form->model()->use_point;
                $refund->money_amount = $form->model()->use_point;
                $refund->quote = '1';
                $refund->bank = '은행';
                $refund->account = '계좌번호';
                $refund->holder = '계좌명';
                $refund->ip = request()->ip();
                $refund->header_info = request()->server('HTTP_USER_AGENT');
                $refund->save();
            }
        });


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

        return $form;
    }
}
