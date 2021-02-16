<?php

namespace App\Admin\Controllers;

use App\Point;
use App\Refund;
use App\Refundquote;
use App\RefundStep;
use App\User;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class RefundController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '출금관리';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        //Permission::check('refund');

        $grid = new Grid(new Refund());

        if (!Admin::user()->inRoles(['administrator', 'master'])){
            //슈퍼 관리자가 아니라면 해당 하부의 일반 회원만 조회
            $user_table = 'users';
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->where('recommends.step1_id', '=', Admin::user()->id)
                ->whereNotNull('step2_id')
                ->whereNotNull('step3_id')
                ->whereNotNull('step4_id')
                ->whereNotNull('step5_id')
                ->select('users.id')->get();

            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->id);
            }

            $grid->model()->whereIn('user_id', $userArray);
        }

        $grid->expandFilter();
        $grid->disableCreateButton();
        //$grid->disableRowSelector();
        //$grid->disableFilter();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('user.username', trans('admin.deposit.user_id_search'));

            $steps = RefundStep::all();
            $steapArr = array();
            foreach ($steps as $step){
                $steapArr[$step->id] = $step->name;
            }
            $filter->equal('step_id', trans('admin.deposit.step'))->select($steapArr);
            $options = [
            ];
            $filter->between('created_at', trans('admin.deposit.created_at'))->datetime($options);
        });
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            //$actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->column('id', trans('admin.deposit.no'));
        $grid->user()->username(trans('admin.deposit.userid'));
        $grid->refundstep()->name(trans('admin.deposit.step'));
        $grid->column('amount', trans('admin.withdrawal.point_amount'))->display(function ($amount){
            return number_format($amount);
        })->sortable();
        $grid->column('bank', trans('admin.deposit.bank'));
        $grid->column('account', trans('admin.deposit.bank_account'));
        $grid->column('holder', trans('admin.deposit.holder'));
        $grid->column('created_at', trans('admin.deposit.created_at'))->sortable();
        $grid->column('updated_at', trans('admin.deposit.updated_at'))->sortable();

        $grid->model()->orderBy('id', 'desc');

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
        //Permission::check('refund');

        $show = new Show(Refund::findOrFail($id));

        $show->panel()
            ->tools(function ($tools) {
                //$tools->disableEdit();
                // $tools->disableList();
                $tools->disableDelete();
            });

        $show->field('id', trans('admin.deposit.no'));
        $show->field('user_id', trans('admin.deposit.userid'))->as(function ($user_id){
            return User::where('id', $user_id)->value('username');
        });
        //$show->field('step_id', __('Step id'));\
        $show->field('step_id', trans('admin.deposit.step'))->as(function ($step_id){
            return RefundStep::where('id', $step_id)->value('name');
        });
        $show->field('amount', trans('admin.withdrawal.point_amount'));
        $show->field('money_amount', trans('admin.withdrawal.money_amount'));
        $show->field('bank', trans('admin.deposit.bank'));
        $show->field('account', trans('admin.deposit.bank_account'));
        $show->field('holder', trans('admin.deposit.holder'));
        $show->field('ip', trans('admin.deposit.ip_info'));
        $show->field('header_info', trans('admin.deposit.header_info'));
        $show->field('created_at', trans('admin.deposit.created_at'));
        $show->field('updated_at', trans('admin.deposit.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        //Permission::check('refund');

        //출근환율조회
        $outquote   = Refundquote::orderBy('id', 'desc')->first();
        $price      = $outquote->price;
        Admin::script("
            $('#amount').keyup(function(){
                var val = $(this).val();
                var outquote = $price;
                var result = val / outquote;
                $('#money_amount').val(result);
            });
        ");

        $form = new Form(new Refund());

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

        /*$form->select('user_id', trans('admin.deposit.user_id_search'))->options(function ($id){
            $user = User::find($id);
            if ($user){
                return [$user->id => $user->username];
            }
        })->ajax('/admin/api/selectusers')->required();*/

        $form->display('user.username', trans('admin.deposit.user_id_search'));

        $steps = RefundStep::all();
        $steapArr = array();
        foreach ($steps as $step){
            $steapArr[$step->id] = $step->name;
        }
       $form->select('step_id', trans('admin.deposit.step'))->options($steapArr)->rules('required');

        $form->text('amount', trans('admin.withdrawal.point_amount'))->readonly();
        $form->text('money_amount', trans('admin.withdrawal.money_amount'))->readonly();
        $form->text('bank', trans('admin.deposit.bank'))->readonly();
        $form->text('account', trans('admin.deposit.bank_account'))->readonly();
        $form->text('holder', trans('admin.deposit.holder'))->readonly();

        if ($form->isCreating()) {
            $form->hidden('quote', __('Quote'))->value($price);
        }
        $form->hidden('ip')->value(\request()->ip());
        $form->hidden('header_info')->value(\request()->header('User-Agent'));
        $form->hidden('admin_id')->value(Admin::user()->id);

        $form->saving(function (Form $form) {
            $refundStep_success = RefundStep::where('code', 'refund_ok')->first();
            $refundStep_fail    = RefundStep::where('code', 'refund_cancel')->first();

            if ($form->step_id && $form->model()->step_id != $form->step_id) {
                if ($form->model()->step_id === $refundStep_success->id || $form->model()->step_id === $refundStep_fail->id) {
                    $error = new MessageBag([
                        'title' => trans('admin.deposit.update_err_title'),
                        'message' => trans('admin.deposit.update_err_msg'),
                    ]);
                    return back()->with(compact('error'));
                }
            }

            //출금 가능한지 데이터 조회
            //잔여 포인트 검색
            /*$add_cnt        = Point::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
            $minus_cnt      = Point::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
            $now_point       = $add_cnt - $minus_cnt;

            if ((int)$now_point < (int)$form->amount){
                $error = new MessageBag([
                    'title' => trans('admin.withdrawal.send_err_title'),
                    'message' => trans('admin.withdrawal.send_err_msg'),
                ]);
                return back()->with(compact('error'));
            }*/
        });

        $form->saved(function (Form $form) {

            //포인트 내역에 차감 내역이 있는지 확인하여 없을때만 작동하게
            //$isData = Point::where('refund_id', $form->model()->id)->first();

            //if ($isData === null) {
                //$refundStepSuccess = RefundStep::where('code', 'refund_ok')->first();
                $refundStepCancel = RefundStep::where('code', 'refund_cancel')->first();

                //취소 하면 다시 포인트 롤백 해준다.
                if ((int)$form->model()->step_id === (int)$refundStepCancel->id) {
                    $point = Point::where('refund_id', $form->model()->id);
                    $point->delete();
                }

                /*if ((int)$form->model()->step_id === (int)$refundStepSuccess->id){
                    //잔여 포인트 검색
                    $add_cnt        = Point::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
                    $minus_cnt      = Point::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
                    $now_point       = $add_cnt - $minus_cnt;

                    //회원의 현재 포인트에서 춮금 되는 포인트 차감
                    $result_point = (int)$now_point - (int)$form->amount;

                    Point::create([
                        'user_id' => $form->model()->user_id,
                        'deposit_id' => $form->model()->id,
                        'po_content' => 'withdraw',
                        'point' => '0',
                        'use_point' => $form->model()->money_amount,
                        'mb_point' => $result_point,
                    ]);
                }*/
            //}

        });

        return $form;
    }
}
