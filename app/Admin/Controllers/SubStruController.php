<?php

namespace App\Admin\Controllers;

use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class SubStruController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '하부구조';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public $sub_cnt = 0;
    protected function grid()
    {
        $step       = (Request::get('step') === null) ? 1 : (int) Request::get('step');
        $sub_step   = Request::get('sub_step');
        $user_id    = Request::get('user_id');

        $userTable = config('admin.database.users_table');
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel());

        //db
        $grid->model()->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id');
        $grid->model()->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id');

        if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master')) {
            if ($step === 1) {
                $grid->model()->whereNotNull('recommends.step1_id');
                $grid->model()->whereNull('recommends.step2_id');
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($step === 2 && $user_id !== null) {
                $grid->model()->where('recommends.step1_id', $user_id);
                $grid->model()->whereNotNull('recommends.step2_id');
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($step === 3 && $user_id !== null) {
                $grid->model()->where('recommends.step2_id', $user_id);
                $grid->model()->whereNotNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($step === 4 && $user_id !== null) {
                $grid->model()->where('recommends.step3_id', $user_id);
                $grid->model()->whereNotNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ($step === 5 && $user_id !== null) {
                $grid->model()->where('recommends.step4_id', $user_id);
                $grid->model()->whereNotNull('recommends.step5_id');
            }
        } else {
            //권한 등급 조회
            if ($user_id === null) {
                //로그인한 회원 권한 조회
                $admin_role         = Admin::user()->roles;
                $admin_role_id      = $admin_role[0]['id'];
            } else {
                $user = DB::table('admin_role_users')->where('user_id', $user_id)->first();
                $admin_role_id = DB::table('admin_roles')->where('id', $user->role_id)->value('id');
            }
            $admin_order = DB::table('admin_roles_order')->where('roles_id', $admin_role_id)->first();

            //권한등급 숫자로 조회할 컬럼명 선언
            $order_num  = $admin_order->orderby -1;
            $low_num    = $admin_order->orderby;

            $admin_step_col = 'step'.$order_num .'_id';
            $low_step_col   = 'step'.$low_num .'_id';

            //권한 자 계정 시퀀스
            $admin_id = $user_id ?? Admin::user()->id;

            if ((int)$order_num === 1) {
                $grid->model()->where('recommends.' . $admin_step_col, $admin_id);
                $grid->model()->whereNotNull($low_step_col);
                $grid->model()->whereNull('recommends.step3_id');
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ((int)$order_num === 2) {
                $grid->model()->where('recommends.' . $admin_step_col, $admin_id);
                $grid->model()->whereNotNull($low_step_col);
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ((int)$order_num === 3) {
                $grid->model()->where('recommends.' . $admin_step_col, $admin_id);
                $grid->model()->whereNotNull($low_step_col);
                $grid->model()->whereNull('recommends.step5_id');
            }
            if ((int)$order_num === 4) {
                $grid->model()->where('recommends.' . $admin_step_col, $admin_id);
                $grid->model()->whereNotNull($low_step_col);
            }
        }




        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            //$actions->disableDelete();
            //$actions->disableEdit();
            //$actions->disableView();
            //$actions->add(new Replicate());
        });
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('username', trans('admin.member.user_id'));
            $options = [
            ];
            //$filter->between('created_at', '등록일')->datetime($options);
            $filter->expand();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->column('id', '번호');

        $grid->column('roles', trans('admin.member.level'))->pluck('name')->label();

        $grid->column('username', trans('admin.member.user_id'));

        $grid->recommend_id(trans('admin.member.referrer'))->display(function ($recommend_id){
            return User::where('id', $recommend_id)->value('username');
        });

        $grid->column('losing_profit', '루징수수료');

        $grid->column('rolling_profit', '롤링 수수료');

        if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master')) {
            switch ($step) {
                case 1:
                    $sub_name = '부본';
                    break;
                case 2:
                    $sub_name = '총판';
                    break;
                case 3:
                    $sub_name = '매장';
                    break;
                case 4:
                    $sub_name = '유저';
                    break;
            }
        } else {
            switch ($low_num) {
                case 1:
                    $sub_name = '부본';
                    break;
                case 2:
                    $sub_name = '총판';
                    break;
                case 3:
                    $sub_name = '매장';
                    break;
                case 4:
                    $sub_name = '유저';
                    break;
                default:
                    $sub_name = '없음';
                    break;
            }
        }

        if ($step < 5) {
            $grid->column($sub_name . ' '. trans('admin.member.count'))->display(function () {

                if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master')) {
                    $step = (Request::get('step') === null) ? 1 : (int)Request::get('step');
                } else {
                    $user_id = Request::get('user_id');
                    if ($user_id === null) {
                        //로그인한 회원 권한 조회
                        $admin_role         = Admin::user()->roles;
                        $admin_role_id      = $admin_role[0]['id'];
                    } else {
                        $user = DB::table('admin_role_users')->where('user_id', $user_id)->first();
                        $admin_role_id = DB::table('admin_roles')->where('id', $user->role_id)->value('id');
                    }
                    $admin_order = DB::table('admin_roles_order')->where('roles_id', $admin_role_id)->first();
                    $step    = $admin_order->orderby;
                }

                switch ($step) {
                    case 1:
                        $sub_cnt = DB::table('recommends')
                            ->where('step1_id', $this->id)
                            ->whereNotNull('step2_id')
                            ->whereNull('step3_id')
                            ->whereNull('step4_id')
                            ->whereNull('step5_id')
                            ->count();
                        break;
                    case 2:
                        $sub_cnt = DB::table('recommends')
                            ->where('step2_id', $this->id)
                            ->whereNotNull('step3_id')
                            ->whereNull('step4_id')
                            ->whereNull('step5_id')
                            ->count();
                        break;
                    case 3:
                        $sub_cnt = DB::table('recommends')
                            ->where('step3_id', $this->id)
                            ->whereNotNull('step4_id')
                            ->whereNull('step5_id')
                            ->count();
                        break;
                    case 4:
                        $sub_cnt = DB::table('recommends')
                            ->where('step4_id', $this->id)
                            ->whereNotNull('step5_id')
                            ->count();
                        break;
                    default: $sub_cnt = 0; break;
                }
                $this->sub_cnt = $sub_cnt;
                return $sub_cnt;
            });
        }

        $grid->column('created_at', trans('admin.member.created_at'));

        if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master')) {
            if ($step < 5) {
                $grid->column(trans('admin.member.lower_view'))->display(function () {
                    $step = (Request::get('step') === null) ? 1 : (int)Request::get('step');
                    $next_step = $step + 1;
                    return  '<a href="subs?step=' . $next_step . '&user_id=' . $this->getKey() . '">클릭</a>';
                });
            }
        } else {
            if ($order_num < 4) {
                $grid->column(trans('admin.member.lower_view'))->display(function () {
                    $step = (Request::get('step') === null) ? 1 : (int)Request::get('step');
                    $next_step = $step + 1;
                    if ($this->sub_cnt === 0){
                        return '';
                    } else {
                        return '<a href="subs?step=' . $next_step . '&user_id=' . $this->getKey() . '">클릭</a>';
                    }
                });
            }
        }

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
        /*$show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        $show->field('email_verified_at', __('Email verified at'));
        $show->field('password', __('Password'));
        $show->field('remember_token', __('Remember token'));
        $show->field('avatar', __('Avatar'));
        $show->field('profit', __('Profit'));
        $show->field('temp_password', __('Temp password'));
        $show->field('bank', __('Bank'));
        $show->field('account', __('Account'));
        $show->field('holder', __('Holder'));
        $show->field('withdrawal_password', __('Withdrawal password'));
        $show->field('phone', __('Phone'));
        $show->field('facebook', __('Facebook'));
        $show->field('activated', __('Activated'));
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
        /*$form = new Form(new User());

        $form->text('username', __('Username'));
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

        return $form;*/
    }
}
