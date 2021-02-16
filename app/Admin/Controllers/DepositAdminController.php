<?php

namespace App\Admin\Controllers;

use App\DepositAdmin;
use App\DepositStep;
use App\Headquarter;
use App\HeadquarterLog;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class DepositAdminController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '본사알신청관리';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DepositAdmin());

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            if (Admin::user()->inRoles(['administrator', 'master'])) {
                $actions->disableEdit();
            }
            $actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        //본사나 마스터는 생성 못하게
        if (Admin::user()->inRoles(['administrator', 'master', 'company'])){
            $grid->disableCreateButton();
        }

        //본사면 본사 하위 계정 만 볼수 있게 한다.
        if (Admin::user()->inRoles(['company'])){
            $user_table = 'users';
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->where('recommends.step1_id', '=', Admin::user()->id)
                ->whereNotNull('step2_id')
                ->whereNotNull('step3_id')
                ->whereNotNull('step4_id')
                ->whereNull('step5_id')
                ->select('users.id')->get();
            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->id);
            }
            $grid->model()->whereIn('user_id', $userArray);
        }

        //하위 계정이만 자신 것만 보게 한다.
        if (Admin::user()->inRoles(['store', 'sub_company', 'distributor'])){
            $grid->model()->where('user_id', Admin::user()->id);
        }

        $grid->model()->orderByDesc('id');


        $grid->column('id', '번호');
        $grid->user()->username(trans('admin.refund_admin.user_id'));
        $grid->depositstep()->name(trans('admin.refund_admin.step'));
        $grid->column('amount', '수량')->display(function ($amount){
            return number_format($amount);
        });
        $grid->column('created_at', '등록일자');
        $grid->column('updated_at', '변경일자');

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
        /*$show = new Show(DepositAdmin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('setp_id', __('Setp id'));
        $show->field('amount', __('Amount'));
        $show->field('bank', __('Bank'));
        $show->field('account', __('Account'));
        $show->field('holder', __('Holder'));
        $show->field('phone', __('Phone'));
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
        $form = new Form(new DepositAdmin());

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
        if ($form->isCreating()) {
            $form->hidden('user_id')->value(Admin::user()->id);
            $form->display('회원 아이디')->value(Admin::user()->username);
        } else {
            $form->display('user.username', trans('admin.deposit.user_id_search'));
        }


        $steps = DepositStep::all();
        $steapArr = array();
        foreach ($steps as $step){
            if ($form->isCreating() && $step->code !== 'reg') {
                continue;
            }
            $steapArr[$step->id] = $step->name;
        }
        $form->select('step_id', trans('admin.deposit.step'))->options($steapArr)->default(1)->rules('required');

        $form->number('amount', '신청할 수량량')->rules('required');

        $form->text('bank', trans('admin.refund_admin.bank'))->default(Admin::user()->bank)->help(trans('admin.refund_admin.help_text'));

        $form->text('account', trans('admin.refund_admin.bank_account'))->default(Admin::user()->account)->help(trans('admin.refund_admin.help_text'));

        $form->text('holder', trans('admin.refund_admin.holder'))->default(Admin::user()->holder)->help(trans('admin.refund_admin.help_text'));

        $form->text('phone', '연락처');

        $form->saving(function (Form $form) {
            $depositstep_success    = DepositStep::where('code','success')->first();
            $depositstep_fail       = DepositStep::where('code','cancel')->first();

            if ($form->step_id && $form->model()->step_id != $form->step_id) {
                if ($form->model()->step_id === $depositstep_success->id || $form->model()->step_id === $depositstep_fail->id) {
                    $error = new MessageBag([
                        'title' => trans('admin.deposit.update_err_title'),
                        'message' => trans('admin.deposit.update_err_msg'),
                    ]);
                    return back()->with(compact('error'));
                }
            }

            if ((int)$form->amount === 0) {
                $error = new MessageBag([
                    'title' => trans('admin.refund_admin.amount_err_title'),
                    'message' => trans('admin.refund_admin.amount_err_msg'),
                ]);
                return back()->with(compact('error'));
            }

            if ((int)$form->amount < 100000) {
                $error = new MessageBag([
                    'title' => '신청 알 수량이 적습니다.',
                    'message' => '100,000 개 이상 알 신청이 가능합니다.',
                ]);
                return back()->with(compact('error'));
            }

            if ($form->isEditing()) {
                //다시 알 현재 보유 확인
                //현재 보유 알 합계
                $add_cnt = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
                $minus_cnt = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
                $in_point = $add_cnt - $minus_cnt;

                if ((int)$form->amount > (int)$in_point) {
                    $error = new MessageBag([
                        'title' => '알이 부족 합니다.',
                        'message' => '현재 출금 신청한 수량보다 보유 알이 적습니다.',
                    ]);
                    return back()->with(compact('error'));
                }
            }
        });

        $form->saved(function (Form $form) {

            if ($form->isCreating()){
                //텔레그램 메시지 전송
                sendTelegramMsgDepositEgg($form->model()->user_id, $form->model()->amount);
            }

            if ($form->isEditing()) {
                //성공 카테고리 검색
                $depositstep_success = DepositStep::where('code', 'success')->first();

                //성공
                if ((int)$form->model()->step_id === (int)$depositstep_success->id) {
                    //본사 포인트 차감 및 로그 작성
                    $h_add_cnt = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
                    $h_minus_cnt = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
                    $h_in_point = $h_add_cnt - $h_minus_cnt;

                    //headquarters 먼저 등록
                    $head = new Headquarter;
                    $head->sender_id = Admin::user()->id;
                    $head->user_id = $form->model()->user_id;
                    $head->amount = $form->model()->amount;
                    $head->save();

                    //headquarters_log 테이블에 등록
                    HeadquarterLog::create([
                        'user_id' => Admin::user()->id,
                        'head_id' => $head->id,
                        'po_content' => 'send_egg',
                        'use_point' => $form->model()->amount,
                        'mb_point' => $h_in_point,
                    ]);

                    //본사 회원 테이블에 있는 알 수량 업데이트
                    $admin = User::find(Admin::user()->id);
                    $admin->egg_amount -= $form->model()->amount;
                    $admin->save();



                    //해당 계정에 알을 넣어 준다
                    $u_add_cnt = HeadquarterLog::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
                    $u_minus_cnt = HeadquarterLog::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
                    $u_in_point = $u_add_cnt - $u_minus_cnt;

                    HeadquarterLog::create([
                        'user_id' => $form->model()->user_id,
                        'head_id' => $head->id,
                        'po_content' => 'receive_charge',
                        'point' => $form->model()->amount,
                        'mb_point' => $u_in_point,
                    ]);

                    //해당 회원 정보 업데이트
                    $user = User::find($form->model()->user_id);
                    $user->egg_amount += $form->model()->amount;
                    $user->save();
                }


            }
        });

        return $form;
    }
}
