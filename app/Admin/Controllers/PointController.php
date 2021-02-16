<?php

namespace App\Admin\Controllers;

use App\Point;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class PointController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '포인트내역';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Point());

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

        $grid->model()->orderBy('id', 'desc');

        $grid->disableActions();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->expandFilter();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('user.username', 'user id');

            //카테고리- 입금 :charge, 게임머니로변환:send_game, 게임머리에서포인트로변환:send_web, 포인트환전:refund, 회원가입:join, 관리자:admin
            $steapArr = [
                'charge' => '입금',
                'send_game' => '게임머니로변환',
                'send_web' => '포인트로변환',
                'withdraw' => '포인트환전',
                'join_event' => '회원가입',
                'admin_charge' => '관리자',
            ];
            $filter->equal('po_content', 'category')->select($steapArr);
            $options = [
            ];
            $filter->between('created_at', trans('admin.deposit.created_at'))->datetime($options);
        });

        $grid->column('id', 'No');
        $grid->user()->username(trans('admin.deposit.userid'));
        //$grid->column('deposit_id', __('Deposit id'));
        $grid->column('po_content', 'category')->using([
            'charge' => '입금',
            'send_game' => '게임머니로변환',
            'send_web' => '포인트로변환',
            'withdraw' => '포인트환전',
            'join_event' => '회원가입',
            'admin_charge' => '관리자',
        ])->sortable();
        /*$grid->column('point', 'Point')->display(function ($point){
            return number_format($point);
        });
        $grid->column('use_point', 'Use point')->display(function ($use_point){
            return number_format($use_point);
        });*/

        $grid->column('not_point', 'Flow point')->display(function (){
            return ((int)$this->point > 0 ) ?  number_format($this->point) : number_format($this->use_point);
        });


        //$grid->column('mb_point', 'Mb point');
        $grid->column('created_at', 'Created at');
        $grid->column('updated_at', 'Updated at');
        //$grid->column('game_id', 'Game id');
        //$grid->column('refund_id', 'Refund id');

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
        $show->field('po_content', __('Po content'));
        $show->field('point', __('Point'));
        $show->field('use_point', __('Use point'));
        $show->field('mb_point', __('Mb point'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('game_id', __('Game id'));
        $show->field('refund_id', __('Refund id'));

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

        $form->select('user_id', trans('admin.deposit.user_id_search'))->options(function ($id){
            $user = User::find($id);
            if ($user){
                return [$user->id => $user->username];
            }
        })->ajax('/api/selectonlyusers')->required();
        $form->hidden('po_content')->value('admin_charge');
        $form->hidden('use_point')->value('0');
        $form->hidden('mb_point');
        $form->number('point', 'Point')->rules('required');

        $form->saving(function (Form $form) {
            $add_cnt =      Point::where('user_id', $form->user_id)->where('use_point', '=', '0')->sum('point');
            $minus_cnt =    Point::where('user_id', $form->user_id)->where('point', '=', '0')->sum('use_point');
            $user_point   = $add_cnt - $minus_cnt;

            $form->mb_point = $user_point;
        });

        return $form;
    }
}
