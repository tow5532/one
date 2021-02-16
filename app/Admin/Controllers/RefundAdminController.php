<?php

namespace App\Admin\Controllers;

use App\DailyinfoBottom;
use App\DailyinfoBottomTotal;
use App\HeadquarterLog;
use App\MoneyLog;
use App\Recommend;
use App\RefundAdmin;
use App\RefundStep;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class RefundAdminController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '하부 출금 신청';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RefundAdmin());

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            $actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        //본사나 마스터는 생성 못하게
        if (Admin::user()->inRoles(['administrator', 'master', 'company'])){
            $grid->disableCreateButton();
        }

        //본사 가 접근 하면 하부 회원들만 보이게
        if (Admin::user()->isRole('company')){
            $user_table = 'users';
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->where('recommends.step1_id', '=', Admin::user()->id)
                ->whereNull('step5_id')
                ->select('users.id')->get();
            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->id);
            }
            $grid->model()->whereIn('user_id', $userArray);
        }
        elseif (Admin::user()->inRoles(['sub_company', 'distributor', 'store'])){
            $grid->model()->where('user_id', Admin::user()->id);
        }

        $grid->model()->orderByDesc('id');



        $grid->column('id', trans('admin.refund_admin.id'));
        $grid->user()->username(trans('admin.refund_admin.user_id'));
        $grid->refundstep()->name(trans('admin.refund_admin.step'));
        $grid->column('amount', trans('admin.refund_admin.amount'))->display(function ($amount){
            return number_format($amount);
        });
        //$grid->column('existing_amount', trans('admin.refund_admin.id'));
        $grid->column('bank', trans('admin.refund_admin.bank'));
        $grid->column('account', trans('admin.refund_admin.bank_account'));
        $grid->column('holder', trans('admin.refund_admin.holder'));
        $grid->column('created_at', trans('admin.refund_admin.created_at'));
        $grid->column('updated_at', '수정일');

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
        /*$show = new Show(RefundAdmin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('admin_id', __('Admin id'));
        $show->field('step_id', __('Step id'));
        $show->field('amount', __('Amount'));
        $show->field('existing_amount', __('Existing amount'));
        $show->field('bank', __('Bank'));
        $show->field('account', __('Account'));
        $show->field('holder', __('Holder'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;*/
    }

    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form($id)->edit($id));
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        $form = new Form(new RefundAdmin());

        //기존에 아직 처리 전인 건수가 있으면 못하게 해아함
        if ($form->isCreating()) {
            $refundStep_reg = RefundStep::where('code', 'refund')->first();
            $reg_cnt = RefundAdmin::where('user_id', Admin::user()->id)->where('step_id', $refundStep_reg->id)->count();
            if ($reg_cnt > 0) {
                $error = new MessageBag([
                    'title' => trans('admin.refund_admin.create_err_title'),
                    'message' => trans('admin.refund_admin.create_err_msg'),
                ]);
                return back()->with(compact('error'));
            }
        }

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

        if ($form->isCreating()){
            $form->hidden('user_id')->value(Admin::user()->id);

            $form->display('회원 아이디')->value(Admin::user()->username);

            //현재 보유 알 합계
            $add_cnt    = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
            $minus_cnt  = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
            $in_point   = $add_cnt - $minus_cnt;

            $form->display('보유 알 수량')->value(number_format($in_point));

            $steps = RefundStep::where('code', '<>', 'refund_ok')->get();
            $steapArr = array();
            foreach ($steps as $step){
                if ($step->code === 'refund_cancel'){
                    continue;
                }
                $steapArr[$step->id] = $step->name;
            }
            $form->select('step_id', trans('admin.refund_admin.step'))->options($steapArr)->default(1)->rules('required');

            $form->number('amount', '출금 신청 수량')->min(1)->max($in_point)->rules('required');
        }

        if ($form->isEditing()){
            $form->display('amount', '출금 신청 알 수량');
            //$form->display('existing_amount');

            if (! Admin::user()->inRoles(['administrator', 'master', 'company'])) {
                $steps = RefundStep::where('code', '<>', 'refund_ok')->get();
            } else {
                $steps = RefundStep::all();
            }

            $steapArr = array();
            foreach ($steps as $step){
                $steapArr[$step->id] = $step->name;
            }
            $form->select('step_id', trans('admin.deposit.step'))->options($steapArr)->default(1)->rules('required');
        }

        //$form->text('existing_amount', 'Existing amount');
        $form->hidden('existing_amount');

        $form->text('bank', trans('admin.refund_admin.bank'))->default(Admin::user()->bank)->help(trans('admin.refund_admin.help_text'));
        $form->text('account', trans('admin.refund_admin.bank_account'))->default(Admin::user()->account)->help(trans('admin.refund_admin.help_text'));
        $form->text('holder', trans('admin.refund_admin.holder'))->default(Admin::user()->holder)->help(trans('admin.refund_admin.help_text'));

        $form->confirm(trans('admin.refund_admin.confirm_msg'), 'create');

        $form->saving(function (Form $form) {

            if ($form->isCreating()) {
                $form->existing_amount = $form->amount;

                if ((int)$form->amount === 0) {
                    $error = new MessageBag([
                        'title' => trans('admin.refund_admin.amount_err_title'),
                        'message' => trans('admin.refund_admin.amount_err_msg'),
                    ]);
                    return back()->with(compact('error'));
                }

                if ((int)$form->amount < 10000) {
                    $error = new MessageBag([
                        'title' => '출금 수량이 적습니다.',
                        'message' => '10,000 개 이상 출금 신청이 가능합니다.',
                    ]);
                    return back()->with(compact('error'));
                }

                //다시 알 현재 보유 확인
                //현재 보유 알 합계
                $add_cnt    = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
                $minus_cnt  = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
                $in_point   = $add_cnt - $minus_cnt;

                if ((int)$form->amount > (int)$in_point){
                    $error = new MessageBag([
                        'title' => '알이 부족 합니다.',
                        'message' => '현재 출금 신청한 수량보다 보유 알이 적습니다.',
                    ]);
                    return back()->with(compact('error'));
                }
            }

            if ($form->isEditing()) {
                //수정시 이미 완료된 건이면 수정 못하게 함
                $refundStep_success = RefundStep::where('code', 'refund_ok')->first();
                $refundStep_fail = RefundStep::where('code', 'refund_cancel')->first();

                if ($form->step_id && $form->model()->step_id != $form->step_id) {
                    if ($form->model()->step_id === $refundStep_success->id || $form->model()->step_id === $refundStep_fail->id) {
                        $error = new MessageBag([
                            'title' => trans('admin.refund_admin.update_err_title'),
                            'message' => trans('admin.refund_admin.update_err_msg'),
                        ]);
                        return back()->with(compact('error'));
                    }
                }
            }
        });


        $form->saved(function (Form $form) {
            if ($form->isCreating()) {
                //잔여 포인트 검색
                $add_cnt = HeadquarterLog::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
                $minus_cnt = HeadquarterLog::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
                $in_point = $add_cnt - $minus_cnt;

                //먼저 알을 차감 시킨다.
                $head_log = new HeadquarterLog;
                $head_log->user_id = $form->model()->user_id;
                $head_log->po_content = 'refund_egg';
                $head_log->point = '0';
                $head_log->use_point = $form->model()->amount;
                $head_log->mb_point = $in_point;
                $head_log->save();

                //회원 알 컬럼 정보도 업데이트
                $user = User::find($form->model()->user_id);
                $user->egg_amount -= $form->model()->amount;
                $user->save();

                //텔레그램 메시지 전송
                sendTelegramMsgAdminRefund($form->model()->user_id, $form->model()->amount);
            }

            if ($form->isEditing()) {
                //본사가 취소 했으면 다시 돌려 준다.
                $refundStep_fail = RefundStep::where('code', 'refund_cancel')->first();

                if ((int)$form->model()->step_id === (int)$refundStep_fail->id) {
                    //잔여 포인트 검색
                    $add_cnt = HeadquarterLog::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
                    $minus_cnt = HeadquarterLog::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
                    $in_point = $add_cnt - $minus_cnt;

                    //알을 먼저 다시 넣어 준다.
                    $head_log = new HeadquarterLog;
                    $head_log->user_id = $form->model()->user_id;
                    $head_log->po_content = 'refund_cancel';
                    $head_log->point = $form->model()->amount;
                    $head_log->use_point = '0';
                    $head_log->mb_point = $in_point;
                    $head_log->save();

                    //회원 알 컬럼 정보도 업데이트
                    $user = User::find($form->model()->user_id);
                    $user->egg_amount += $form->model()->amount;
                    $user->save();
                }

                //승인 일경우 본사 알에 더해 준다.
                $refundStep_success = RefundStep::where('code', 'refund_ok')->first();

                if ((int)$form->model()->step_id === (int)$refundStep_success->id) {
                    $recommend = Recommend::where('user_id', $form->model()->user_id)->first();
                    $company_id = $recommend->step1_id;

                    //잔여 알 검색
                    $add_cnt = HeadquarterLog::where('user_id', $company_id)->where('use_point', '=', '0')->sum('point');
                    $minus_cnt = HeadquarterLog::where('user_id', $company_id)->where('point', '=', '0')->sum('use_point');
                    $in_point = $add_cnt - $minus_cnt;

                    $head_log = new HeadquarterLog;
                    $head_log->user_id = $company_id;
                    $head_log->po_content = 'refund_company';
                    $head_log->point = $form->model()->amount;
                    $head_log->use_point = '0';
                    $head_log->mb_point = $in_point;
                    $head_log->save();

                    //회원 알 컬럼 정보도 업데이트
                    $user = User::find($company_id);
                    $user->egg_amount += $form->model()->amount;
                    $user->save();
                }
            }

        });


        return $form;
    }
}
