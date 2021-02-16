<?php

namespace App\Admin\Controllers;

use App\Deposit;
use App\DepositStep;
use App\HeadquarterLog;
use App\Inquote;
use App\Point;
use App\Recommend;
use App\User;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class DepositController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '회원 입금 관리';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */


    protected function grid()
    {
        //Permission::check('deposit');

        $grid = new Grid(new Deposit());

        $grid->disableCreateButton();
        //$grid->disableRowSelector();
        //$grid->disableFilter();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            //$actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

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
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('user.username', trans('admin.deposit.user_id_search'));

            $steps = DepositStep::all();
            $steapArr = array();
            foreach ($steps as $step){
                $steapArr[$step->id] = $step->name;
            }
            $filter->equal('step_id', trans('admin.deposit.step'))->select($steapArr);

            $options = [
            ];
            $filter->between('created_at', trans('admin.deposit.created_at'))->datetime($options);
        });

        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', trans('admin.deposit.no'))->sortable();

        $grid->user()->username(trans('admin.deposit.userid'));

        $grid->column('amount', trans('admin.deposit.cash_amount'))->display(function ($amount){
            return number_format($amount);
        })->sortable();

        $grid->column('charge_amount', trans('admin.deposit.deposit_amount'))->display(function ($charge_amount){
            return number_format($charge_amount);
        })->sortable();

        $grid->depositstep()->name(trans('admin.deposit.step'));
        //$grid->column('sender', __(trans('admin.deposit.sender')));

        $grid->column('created_at', trans('admin.deposit.created_at'))->sortable();

        $grid->column('updated_at', trans('admin.deposit.updated_at'))->sortable();

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
        //Permission::check('deposit');

        $show = new Show(Deposit::findOrFail($id));

        $show->panel()
            ->tools(function ($tools) {
                //$tools->disableEdit();
                // $tools->disableList();
                $tools->disableDelete();
            });

        $show->field('id', __(trans('admin.deposit.no')));
        $show->field('user_id', trans('admin.deposit.userid'))->as(function ($user_id){
            return User::where('id', $user_id)->value('username');
        });
        $show->field('step_id', trans('admin.deposit.step'))->as(function ($step_id){
            return DepositStep::where('id', $step_id)->value('name');
        });
        $show->field('amount', trans('admin.deposit.cash_amount'));
        $show->field('charge_amount', trans('admin.deposit.deposit_amount'));
        $show->field('bank', trans('admin.deposit.bank'));
        $show->field('account', trans('admin.deposit.bank_account'));
        $show->field('holder',trans('admin.deposit.holder'));
        $show->field('sender', trans('admin.deposit.sender'));
        $show->field('header_info', trans('admin.deposit.header_info'));
        $show->field('ip', trans('admin.deposit.ip_info'));
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
        //Permission::check('deposit');

        //충천환율조회
        $inquoute   = Inquote::orderBy('id', 'desc')->first();
        $price      = $inquoute->price;
        Admin::script("
            $('#amount').keyup(function(){
                var val = $(this).val();
                var inquote = $price;
                var result = val * inquote;
                $('#charge_amount').val(result);
            });
        ");

        $form = new Form(new Deposit());

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

        $steps = DepositStep::all();
        $steapArr = array();
        foreach ($steps as $step){
            if ($step->code === 'cancel'){
                continue;
            }
            $steapArr[$step->id] = $step->name;
        }
        $form->select('step_id', trans('admin.deposit.step'))->options($steapArr)->rules('required');

        $form->text('amount', trans('admin.deposit.cash_amount'))->rules('required')->readonly();
        $form->text('charge_amount', trans('admin.deposit.deposit_amount'))->readonly();

        $form->text('sender', trans('admin.deposit.sender'));

        $form->text('bank', trans('admin.deposit.bank'));

        $form->text('account', trans('admin.deposit.bank_account'));

        $form->text('holder', trans('admin.deposit.holder'));

        //$form->number('bonus_point', trans('admin.deposit.bonus'))->help(trans('admin.deposit.bonus_help'));
        $form->hidden('bonus_point')->value('0');

        $form->hidden('ip', 'IP')->value(\request()->ip());
        $form->hidden('header_info')->value(\request()->header('User-Agent'));
        $form->hidden('admin_id')->value(Admin::user()->id);

        if ($form->isCreating()) {
            $form->hidden('quote')->value($price);
        }

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

            //본사 계정에 서 구입한 포인트가 얼마나 있는지 확인
            $company = Recommend::where('user_id', $form->model()->user_id)->first();
            $add_cnt    = HeadquarterLog::where('user_id', $company->step1_id)->where('use_point', '=', '0')->sum('point');
            $minus_cnt  = HeadquarterLog::where('user_id', $company->step1_id)->where('point', '=', '0')->sum('use_point');
            $in_point   = $add_cnt - $minus_cnt;
           //dd((int)$in_point, (int)$form->model()->charge_amount);

            //본사가 소유하고 있는 수량 보다 신청 액수가 크면 에러 발생
            if ((int)$in_point < (int)$form->model()->charge_amount){
                $error = new MessageBag([
                    'title' => trans('admin.deposit.head_amount_err_title'),
                    'message' => trans('admin.deposit.head_amount_err_msg'),
                ]);
                return back()->with(compact('error'));
            }
        });

        $form->saved(function (Form $form) {
            //기존 데이터 있으면 업데이트 안되게 해야함
            $isData = Point::where('deposit_id', $form->model()->id)->first();

            if ($isData === null) {
                //성공 카테고리 검색
                $depositstep_success = DepositStep::where('code', 'success')->first();

                if ((int)$form->model()->step_id === (int)$depositstep_success->id) {
                    $company = Recommend::where('user_id', $form->model()->user_id)->first();
                    //본사 포인트 차감 및 로그 작성
                    $h_add_cnt = HeadquarterLog::where('user_id', $company->step1_id)->where('use_point', '=', '0')->sum('point');
                    $h_minus_cnt = HeadquarterLog::where('user_id', $company->step1_id)->where('point', '=', '0')->sum('use_point');
                    $h_in_point = $h_add_cnt - $h_minus_cnt;
                    //$left_point = (int)$h_in_point - (int)$form->model()->charge_amount;
                    HeadquarterLog::create([
                        'user_id' => $company->step1_id,
                        'deposit_id' => $form->model()->id,
                        'po_content' => 'member_charge',
                        'use_point' => $form->model()->charge_amount,
                        'mb_point' => $h_in_point,
                    ]);

                    //관리자 회원 테이블에 있는 알 수량 업데이트
                    $admin = User::find($company->step1_id);
                    $admin->egg_amount -= $form->model()->charge_amount;
                    $admin->save();

                    //잔여 포인트 검색
                    $add_cnt = Point::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
                    $minus_cnt = Point::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
                    $in_point = $add_cnt - $minus_cnt;

                    Point::create([
                        'user_id' => $form->model()->user_id,
                        'deposit_id' => $form->model()->id,
                        'po_content' => 'charge',
                        'point' => $form->model()->charge_amount,
                        'use_point' => '0',
                        'mb_point' => $in_point,
                    ]);

                    //보너스 포인트 폼에 금액이 0보다 크다면
                    if ((int)$form->model()->bonus_point > 0 ){
                        //잔여 포인트 검색
                        $add_cnt = Point::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
                        $minus_cnt = Point::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
                        $in_point = $add_cnt - $minus_cnt;

                        Point::create([
                            'user_id' => $form->model()->user_id,
                            'deposit_id' => '0',
                            'po_content' => 'admin_charge',
                            'point' => $form->model()->bonus_point,
                            'use_point' => '0',
                            'mb_point' => $in_point,
                        ]);

                        //관리자 회원 테이블에 있는 알 수량 업데이트
                        $admin = User::find($company->step1_id);
                        $admin->egg_amount -= $form->model()->bonus_point;
                        $admin->save();
                    }
                }
            }
        });

        return $form;
    }
}
