<?php

namespace App\Admin\Controllers;

use App\DailyinfoBottom;
use App\DailyinfoBottomTotal;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;

class DailyinfoBottomTotalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Sales';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DailyinfoBottomTotal());

        $url_seg = request()->segment(2);
        $seqArray  = explode('-', $url_seg);
        $level = $seqArray[1];

        //$grid->model()->orderBy('id', 'desc');

        $grid->model()->where('level', '=', $level);

        //어드민 계정이 아닐경우 조건문 을 추가 한다.
        if (!Admin::user()->isRole('administrator') && !Admin::user()->isRole('master') && !Admin::user()->isRole('company')) {

           $roles = Admin::user()->roles;
           $thisRole = '';
           foreach ($roles as $role){
               $thisRole = $role->slug;
           }

           //자기 권한에 자기 페이지라면
            if ($level === $thisRole){
                $grid->model()->where('user_id', Admin::user()->id);
            }
            else {
                $userTable = config('admin.database.users_table');

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


                if ($level === 'distributor'){
                    $users = DB::table($userTable)
                        ->rightJoin('recommends', $userTable . '.id', '=', 'recommends.user_id')
                        ->where('recommends.' . $step_col, '=', Admin::user()->id)
                        ->whereNotNull('recommends.step3_id')
                        ->whereNull ('recommends.step4_id')
                        ->whereNull ('recommends.step5_id')
                        ->select('users.id')->get();
                }
                elseif ($level === 'store'){
                    $users = DB::table($userTable)
                        ->rightJoin('recommends', $userTable . '.id', '=', 'recommends.user_id')
                        ->where('recommends.' . $step_col, '=', Admin::user()->id)
                        ->whereNotNull('recommends.step3_id')
                        ->whereNotNull ('recommends.step4_id')
                        ->whereNull ('recommends.step5_id')
                        ->select('users.id')->get();
                }


                $userArray = array();
                foreach ($users as $user) {
                    array_push($userArray, $user->id);
                }

                $grid->model()->whereIn('user_id', $userArray);
            }
        }


        $grid->column('username', trans('admin.sales_sub.user_id'))->style('text-align:right;');

        $grid->column(trans('admin.sales_sub.total_user_payment'))->display(function (){
            $payment = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('total_payment');
            return number_format($payment);
        })->style('text-align:right;');

        $grid->column(trans('admin.sales_sub.total_user_exchange'))->display(function (){
            $refund = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('total_refund');
            return number_format($refund);
        })->style('text-align:right;');

        $grid->column(trans('admin.sales_sub.total_user_chips'))->display(function (){
            $chips = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('user_chips');
            return number_format($chips);
        })->style('text-align:right;');

        $grid->column(trans('admin.sales_sub.total_user_safe'))->display(function (){
            $safe = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('user_safe');
            return number_format($safe);
        })->style('text-align:right;');

        $grid->column(trans('admin.sales_sub.total_user_deposit'))->display(function (){
            $deposit = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('user_deposit');
            return number_format($deposit);
        })->style('text-align:right;');

        $grid->column(trans('admin.sales_sub.profit'))->display(function (){
            $rev = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('rev');
            return number_format($rev);
        })->style('text-align:right;');

        $grid->disableCreateButton();
        $grid->disableFilter();
        //$grid->disableRowSelector();
        //$grid->disableExport();
        //$grid->disableActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
            $filter->expand();
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
        $show = new Show(DailyinfoBottomTotal::findOrFail($id));

        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
            });


        $show->field('username', trans('admin.sales_sub.user_id'));

        $show->field(trans('admin.sales_sub.total_user_payment'))->as(function (){
            $payment = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('total_payment');
            return number_format($payment);
        });
        $show->field(trans('admin.sales_sub.total_user_exchange'))->as(function (){
            $refund = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('total_refund');
            return number_format($refund);
        });

        $show->field(trans('admin.sales_sub.total_user_chips'))->as(function (){
            $chips = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('user_chips');
            return number_format($chips);
        });

        $show->field(trans('admin.sales_sub.total_user_safe'))->as(function (){
            $safe = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('user_safe');
            return number_format($safe);
        });

        $show->field(trans('admin.sales_sub.total_user_deposit'))->as(function (){
            $deposit = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('user_deposit');
            return number_format($deposit);
        });

        $show->field(trans('admin.sales_sub.profit'))->as(function (){
            $rev = DailyinfoBottom::where('user_id', $this->user_id)
                ->sum('rev');
            return number_format($rev);
        });

        $show->field(trans('admin.member.belong'))->unescape()->as(function (){

            //로그인한 회원 권한 조회
            $admin_role         = Admin::user()->roles;
            $admin_role_id      = $admin_role[0]['id'];
            $admin_role_slug    = $admin_role[0]['slug'];

            $admin_order        = DB::table('admin_roles_order')->where('roles_id', $admin_role_id)->first();

            $admin_order_num    = ($admin_role_slug === 'administrator' || $admin_role_slug === 'master') ? 1 : $admin_order->orderby;

            $recommend          = DB::table('recommends')->where('user_id', $this->user_id)->first();

            $roleOrders = DB::table('admin_roles')
                ->join('admin_roles_order', 'admin_roles.id', '=', 'admin_roles_order.roles_id')
                ->where('admin_roles.slug', '<>', 'master')
                ->select('admin_roles.name', 'admin_roles_order.orderby')
                ->get();

            $headerArr = array();
            $rowArr = array();
            $loopcnt = 1;


            foreach ($roleOrders as $row){
                if ($row->orderby >= $admin_order_num){
                    array_push($headerArr, $row->name);
                    $numStr = 'step'.$loopcnt.'_id';
                    $step_id = $recommend->$numStr;
                    $step1_user = User::find($step_id);
                    if ($step1_user) {
                        array_push($rowArr, $step1_user->username);
                    }
                    //어드민 이나 마스터 계정일 경우는 전부 보여준다
                    if ($admin_role_slug === 'administrator' || $admin_role_slug === 'master'){

                    }
                }
                $loopcnt++;
            }

            $headers    = $headerArr;
            $rows       = [$rowArr];
            $table      = new Table($headers, $rows);

            return $table->render();
        });


        $show->bottomlist('List', function ($bottomlist){
            $bottomlist->resource('/admin/dailyinfo_bottom');
            $bottomlist->model()->orderBy('id', 'desc');

            $bottomlist->search_date(trans('admin.sales_sub.date'))->style('text-align:right;');

            $bottomlist->total_payment(trans('admin.sales_sub.total_user_payment'))->display(function ($total_payment){
               return number_format($total_payment);
            })->style('text-align:right;');
            $bottomlist->total_refund(trans('admin.sales_sub.total_user_exchange'))->display(function ($total_refund){
                return number_format($total_refund);
            })->style('text-align:right;');
            $bottomlist->user_chips(trans('admin.sales_sub.total_user_chips'))->display(function ($user_chips){
                return number_format($user_chips);
            })->style('text-align:right;');
            $bottomlist->user_safe(trans('admin.sales_sub.total_user_safe'))->display(function ($user_safe){
                return number_format($user_safe);
            })->style('text-align:right;');
            $bottomlist->user_deposit(trans('admin.sales_sub.total_user_deposit'))->display(function ($user_deposit){
                return number_format($user_deposit);
            })->style('text-align:right;');
            $bottomlist->rev(trans('admin.sales_sub.profit'))->display(function ($rev){
                return number_format($rev);
            })->style('text-align:right;');

            $bottomlist->disableCreateButton();
            $bottomlist->disableFilter();
            $bottomlist->disableActions();
            $bottomlist->disableRowSelector();
            //$bottomlist->disableExport();
        });

        $show->moneyloglist('Money Logs', function ($moneyloglist) {
            $moneyloglist->resource('/admin/money-logs');
            $moneyloglist->model()->orderBy('id', 'desc');

            $moneyloglist->id(trans('admin.money_log.id'))->display(function ($id){
                return $id;
            });

            $moneyloglist->search_date(trans('admin.money_log.date'))->display(function ($search_date){
                return $search_date;
            });

            $moneyloglist->rev_amount(trans('admin.money_log.rev_amount'))->display(function ($rev_amount){
                return number_format($rev_amount);
            });

            $moneyloglist->chg_amount(trans('admin.money_log.chg_amount'))->display(function ($chg_amount){

                return ($this->reason === 'refund') ? '-'. number_format($chg_amount) : number_format($chg_amount);
            });

            $moneyloglist->now_amount(trans('admin.money_log.now_amount'))->display(function ($now_amount){
                return number_format($now_amount);
            });

            $moneyloglist->reason(trans('admin.money_log.reason'))->display(function ($reason){
                return $reason;
            });

            $moneyloglist->created_at(trans('admin.money_log.created_at'))->display(function ($created_at){
                return  $created_at;
            });

            $moneyloglist->disableCreateButton();
            $moneyloglist->disableFilter();
            $moneyloglist->disableActions();
            $moneyloglist->disableRowSelector();
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
        $form = new Form(new DailyinfoBottomTotal());

        /*$form->text('level', __('Level'));
        $form->number('user_id', __('User id'));
        $form->text('username', __('Username'));
        $form->text('total_payment', __('Total payment'));
        $form->text('total_refund', __('Total refund'));
        $form->text('user_chips', __('User chips'));
        $form->text('user_safe', __('User safe'));
        $form->text('user_deposit', __('User deposit'));
        $form->text('rev', __('Rev'));*/

        return $form;
    }
}
