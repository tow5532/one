<?php

namespace App\Admin\Controllers;

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

class HeadquarterPublicController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '알 내리기';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Headquarter());

        if (! Admin::user()->inRoles(['administrator', 'master'])) {
            $grid->model()->where('user_id', Admin::user()->id);
            $grid->model()->orWhere('sender_id', Admin::user()->id);
        }
        $grid->model()->orderBy('id', 'desc');

        $grid->column('sender_id', '보낸사람')->display(function ($sender_id){
            //등급 조회

            $user = User::find($sender_id);
            return $user->username;
        });
        $grid->column('user_id', '받는사람')->display(function ($user_id){
            $user = User::find($user_id);
            return $user->username;
        });
        $grid->column('amount', '수량')->display(function ($amount){
            return number_format($amount);
        });

        $grid->column('created_at', '등록일자');

        if (! Admin::user()->inRoles(['administrator', 'master'])) {
            $grid->footer(function ($query) {

                // 현재 회원의 알 남은양 조회
                $add_cnt = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
                $minus_cnt = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
                $in_point = $add_cnt - $minus_cnt;
                return "<div style='padding: 10px;'>현재 남은 알 수량 ： $in_point</div>";
            });
        }

        $grid->disableActions();
        //$grid->disableCreateButton();
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
        /*$show = new Show(Headquarter::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('amount', __('Amount'));
        $show->field('bonus_percent', __('Bonus percent'));
        $show->field('bonus_amount', __('Bonus amount'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('full_ok', __('Full ok'));

        return $show;*/
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Headquarter());
        $userTable = config('admin.database.users_table');

        //회원 등급 조회
        if (Admin::user()->inRoles(['administrator', 'master'])) {
            $users = DB::table('users')
                ->join('admin_role_users', 'users.id', '=', 'admin_role_users.user_id')
                ->join('admin_roles', 'admin_role_users.role_id', '=', 'admin_roles.id')
                ->where('admin_roles.slug', 'company')
                ->select('users.id', 'users.username')->get();
        }
        elseif (Admin::user()->isRole('company')) {
            $users = DB::table('users')
                ->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id')
                ->where('recommends.step1_id', Admin::user()->id)
                ->whereNotNull('recommends.step2_id')
                ->whereNull('recommends.step3_id')
                ->whereNull('recommends.step4_id')
                ->whereNull('recommends.step5_id')
                ->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id')->get();
        }
        elseif (Admin::user()->isRole('sub_company')) {
            $users = DB::table('users')
                ->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id')
                ->where('recommends.step2_id', Admin::user()->id)
                ->whereNotNull('recommends.step3_id')
                ->whereNull('recommends.step4_id')
                ->whereNull('recommends.step5_id')
                ->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id')->get();
        }
        elseif (Admin::user()->isRole('distributor')) {
            $users = DB::table('users')
                ->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id')
                ->where('recommends.step3_id', Admin::user()->id)
                ->whereNotNull('recommends.step4_id')
                ->whereNull('recommends.step5_id')
                ->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id')->get();
        }

        $userArr = array();
        foreach ($users as $user){
            $userArr[$user->id] = $user->username;
        }

        if ($form->isCreating()){
            //관리자가 아닐경우는 현재 잔액 출력
            if (! Admin::user()->inRoles(['administrator', 'master'])) {
                //잔여 포인트 검색
                $add_cnt        = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
                $minus_cnt      = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
                $in_point       = $add_cnt - $minus_cnt;
                $form->display('현재남은 수량')->value(number_format($in_point));
            }
        }

        $form->select('user_id', trans('admin.headquarter_charge.user_id'))->options($userArr)->rules('required');

        $form->number('amount', trans('admin.headquarter_charge.amount'))->rules('required');

        $form->hidden('sender_id')->value(Admin::user()->id);


        $form->saving(function (Form $form) {

            if (! Admin::user()->inRoles(['administrator', 'master'])) {
                $add_cnt = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
                $minus_cnt = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
                $in_point = $add_cnt - $minus_cnt;

                if ((int)$form->amount > $in_point) {
                    $error = new MessageBag([
                        'title' => '수량이 너무 많습니다.',
                        'message' => '보내는 수량이 보유 량 보다 많습니다.',
                    ]);
                    return back()->with(compact('error'));
                }
            }

        });

        $form->saved(function (Form $form) {

            //잔여 포인트 검색
            $add_cnt        = HeadquarterLog::where('user_id', $form->model()->user_id)->where('use_point', '=', '0')->sum('point');
            $minus_cnt      = HeadquarterLog::where('user_id', $form->model()->user_id)->where('point', '=', '0')->sum('use_point');
            $in_point       = $add_cnt - $minus_cnt;

            //$point = $form->model()->amount + $form->model()->bonus_amount;
            $point = $form->model()->amount;

            //받는 회원에게는 추가수량 로그 등록
            HeadquarterLog::create([
                'user_id' => $form->model()->user_id,
                'head_id' => $form->model()->id,
                'po_content' => 'receive_charge',
                'point' => $point,
                'mb_point' => $in_point,
            ]);

            $user = User::find($form->model()->user_id);
            $user->egg_amount += $point;
            $user->save();



            //마스터가 아닐경우만 로그 등록
            if (! Admin::user()->inRoles(['administrator', 'master'])) {
                //잔여 포인트 검색
                $add_cnt    = HeadquarterLog::where('user_id', $form->model()->sender_id)->where('use_point', '=', '0')->sum('point');
                $minus_cnt  = HeadquarterLog::where('user_id', $form->model()->sender_id)->where('point', '=', '0')->sum('use_point');
                $in_point   = $add_cnt - $minus_cnt;

                //보낸 회원은 차감 로그 등록
                HeadquarterLog::create([
                    'user_id' => $form->model()->sender_id,
                    'head_id' => $form->model()->id,
                    'po_content' => 'send_egg',
                    'use_point' => $point,
                    'mb_point' => $in_point,
                ]);

                $user = User::find($form->model()->sender_id);
                $user->egg_amount -= $point;
                $user->save();
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
