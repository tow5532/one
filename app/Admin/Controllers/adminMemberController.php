<?php

namespace App\Admin\Controllers;

use App\Recommend;
use App\Roleorder;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;

class adminMemberController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '관리자용회원목록';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userTable = config('admin.database.users_table');
        $userModel = config('admin.database.users_model');
        $grid = new Grid(new $userModel());
        $grid->model()->whereNotIn('username', ['admin', 'master']);
        $grid->model()->where('activated', '1');
        $grid->model()->orderByDesc('id');

        $grid->column('id', '번호');
        $grid->column('roles', trans('admin.member.level'))->pluck('name')->label();
        $grid->column('username', '아이디');
        $grid->column('rolling_profit', '롤링 수수료율');
        $grid->column('losing_profit', '루징 수수료율');
        $grid->created_at(trans('admin.member.created_at'));
        $grid->updated_at(trans('admin.member.updated_at'));

        //$grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            //$actions->disableView();
        });
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('username', trans('admin.member.user_id'));
            $filter->between('created_at', '등록일')->datetime();
            $filter->expand();
        });
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
        $userModel = config('admin.database.users_model');
        $show = new Show($userModel::findOrFail($id));

        $show->field('username', trans('admin.member.user_id'));
        $show->field('withdrawal_password', trans('admin.member.withdrawal'));
        $show->field('profit', trans('admin.member.profit'));
        $show->field('losing_profit', '루징수수료율');
        $show->field('bank', trans('admin.member.bank'));
        $show->field('account', trans('admin.member.account'));
        $show->field('holder', trans('admin.member.holder'));
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        $show->divider();

        $show->field(trans('admin.member.belong'))->unescape()->as(function (){

            $recommend = DB::table('recommends')->where('user_id', $this->id)->first();
            $roleOrders = DB::table('admin_roles')
                ->join('admin_roles_order', 'admin_roles.id', '=', 'admin_roles_order.roles_id')
                ->where('admin_roles.slug', '<>', 'master')
                ->select('admin_roles.name', 'admin_roles_order.orderby')
                ->get();

            $headerArr = array();
            $rowArr = array();
            $loopcnt = 1;

            foreach ($roleOrders as $row){
                array_push($headerArr, $row->name);
                $numStr = 'step'.$loopcnt.'_id';
                $step_id = $recommend->$numStr;
                $step1_user = User::find($step_id);
                if ($step1_user) {
                    array_push($rowArr, $step1_user->username);
                }
                $loopcnt++;
            }

            $headers    = $headerArr;
            $rows       = [$rowArr];
            $table      = new Table($headers, $rows);

            return $table->render();
        });

        $show->field(trans('admin.member.referrer'))->as(function (){
            $recommend = DB::table('recommends')->where('user_id', $this->id)->first();
            if ($recommend->recommend_id === null || $recommend->recommend_id === 0){
                return '';
            } else {
                $user = User::find($recommend->recommend_id);
                return $user->username;
            }
        });

        $show->panel()->tools(function ($tools) {
            //$tools->disableEdit();
            // $tools->disableList();
            $tools->disableDelete();
        });

        return $show;
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
        $form = new Form(new User());

        $form->model()->makeVisible('password');

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

        /*$form->divider('등급설정');

        $form->select('본사')->options(function (){
            $users = Recommend::with('user')
                ->whereNotNull ('step1_id')
                ->whereNull('step2_id')
                ->get();
            $list = array();
            foreach ($users as $user)
            {
                $list[$user->user->id] = $user->user->username;
            }
            return $list;
        })->load('sub_company', '/api/select-sub-company');

        $form->select('sub_company', '부본');*/



        $form->divider('필수정보');

        if($form->isCreating()) {
            $form->text('username', trans('admin.member.user_id'))
                ->creationRules([
                    'required',
                    'string',
                    'min:6',
                    'max:12',
                    'alpha_num',
                    "unique:users",
                ])
                ->updateRules(['required', "unique:users,username,{{id}}"]);
            $form->hidden('temp_password');
            $form->hidden('name');
        }

        if ($form->isEditing()) {
            $role_id    = DB::table('admin_role_users')->where('user_id', $id)->value('role_id');
            $role_name  = DB::table('admin_roles')->where('id', $role_id)->value('name');
            //$form->display(trans('admin.member.level'))->value($role_name);
            $form->text(trans('admin.member.level'))->default($role_name)->readonly();
            $form->display('username', trans('admin.member.user_id'));
            $form->hidden('temp_password');
        }

        $form->password('password', trans('admin.member.password'))->rules([
            'required',
            'min:6',
        ])->default(function ($form) {
            return $form->model()->password;
        });

        $form->password('password_confirm', trans('admin.member.password_confirm'))->rules([
            'required',
            'same:password',
        ]) ->default(function ($form) {
            return $form->model()->password;
        });

        //회원이 일반회원일경우 수수료폼을 비노출 시켜준다.
        $admin_role_slug = null;
        if ($id !== null){
            $user = DB::table('admin_role_users')->where('user_id', $id)->first();
            $admin_role_slug = DB::table('admin_roles')->where('id', $user->role_id)->value('slug');
        }

        //일반회원일경우 비노출
        if ($admin_role_slug !== 'user') {
            $form->divider('수수료설정');
            if ($form->isCreating()) {
                $profit_max = 100;
                $profit_display_max = 100;

                if (!Admin::user()->inRoles(['administrator', 'master'])) {
                    $above = rightAboveRecommend(Admin::user()->id);
                    $profit_max = (int)$above->profit;
                    $profit_display_max = $profit_max;
                }

                $help_txt = (Admin::user()->inRoles(['company', 'master', 'administrator'])) ? 'JackPot [10]' : null;
                $form->number('profit', trans('admin.member.profit') . '  (Max : '. $profit_display_max. ')')
                    ->max($profit_max)->rules(['required']);
            } else {
                if ((int)$id === Admin::user()->id) {
                    $form->display('profit', trans('admin.member.profit'));
                } else {
                    //해당 회원 상위 검색
                    if ($id !== null) {
                        if ($admin_role_slug !== 'company') {
                            $above = rightAboveRecommendEdit($id);
                            $profit_max = (int)$above->profit;

                            $profit_display_max = $profit_max;

                            $help_txt = (Admin::user()->inRoles(['company', 'master', 'administrator'])) ? 'JackPot [10]' : null;

                            $user = DB::table('admin_role_users')->where('user_id', $id)->first();
                            $admin_role_slug = DB::table('admin_roles')->where('id', $user->role_id)->value('slug');
                            $main_arr = array('distributor', 'store');
                            //부본 이하일경우는 10프로 멘트 비노출
                            if (in_array($admin_role_slug, $main_arr, true)) {
                                $help_txt = '';
                            }

                            $form->number('profit', trans('admin.member.profit') . '  (Max : ' . $profit_display_max . ')')
                                ->min(0)->max($profit_max)->rules(['required']);

                        } else {
                            $form->display('profit', trans('admin.member.profit'));
                        }
                    }
                }
            }

            $form->number('losing_profit', '루징 수수료율')->default(0);
        }

        $form->ignore('password_confirm');

        //일반회원 생성시만 등록가능하게 한다.
        if ($admin_role_slug === 'user') {
            $form->divider('기타정보');

            $form->text('withdrawal_password', trans('admin.member.withdrawal'))->rules([
                'max:4',
            ]);

            $form->text('phone', trans('admin.member.phone'));

            $form->text('bank', trans('admin.member.bank'));

            $form->text('account', trans('admin.member.account'));

            $form->text('holder', trans('admin.member.holder'));
        }

        $form->display('created_at', trans('admin.member.created_at'));
        $form->display('updated_at', trans('admin.member.updated_at'));


        $roles = Admin::user()->roles;
        foreach ($roles as $role) {
            $roleName = $role['name'];
            $roleSlug = $role['slug'];
            $role_id = $role['id'];
        }

        $roleOrder = Roleorder::where('roles_id', $role_id)->select('orderby')->first();
        $order_id = ($roleOrder === null && $roleSlug === 'administrator') ? 1 : $roleOrder->orderby;

        $last_order = DB::table('admin_roles_order')->orderBy('id', 'desc')->first();

        if ($order_id + 1 !== (int)$last_order->orderby){
            $form->hidden('admin_yn')->default('Y');
        }


        $form->submitted(function (Form $form){
            /*if (is_null($form->password)) {
                $form->ignore('password');
            }*/
            $form->ignore(trans('admin.member.level'));
        });

        $form->saving(function (Form $form){
            if ($form->isCreating()) {
                $form->temp_password = $form->password;
            }
            if ($form->password && $form->model()->password != $form->password) {
                $form->temp_password = $form->password;
                $form->password = bcrypt($form->password);
            }

            //수정시에만 작동되게
            if($form->isEditing()) {
                $user = DB::table('admin_role_users')->where('user_id', $form->model()->id)->first();
                $admin_role_slug = DB::table('admin_roles')->where('id', $user->role_id)->value('slug');
                if ($admin_role_slug !== 'user' && $admin_role_slug !== 'company') {
                    //dd($form->model()->profit, $form->profit);
                    if ((int)$form->model()->profit !== (int)$form->profit) {
                        ProfitChange::create([
                            'user_id' => $form->model()->id,
                            'profit' => $form->model()->profit,
                            'chg_profit' => $form->profit,
                        ]);
                        //변경된거 무시하고 기존과 동일하게 업데이트
                        $form->profit = $form->model()->profit;
                    }
                }
            }
        });


        $form->saved(function (Form $form) {

            //등록시만 작동하게
            if ($form->isCreating()) {
                //activated 1
                $userTable = config('admin.database.users_table');
                DB::table($userTable)->where('id', $form->model()->id)
                    ->update([
                        'activated' => '1',
                        'temp_password' => $form->model()->temp_password,
                    ]);

                //게임DB 회원테이블에 등록
                /*DB::connection('mssql')->table('Account')->insert([
                    //AccountID, PassWord, LoginType
                    'AccountID' => $form->model()->username,
                    'PassWord' => $form->model()->temp_password,
                    'LoginType' => 'sunpoker',
                ]);*/

                $roles = Admin::user()->roles;
                foreach ($roles as $role) {
                    $roleName = $role['name'];
                    $roleSlug = $role['slug'];
                    $role_id = $role['id'];
                }

                $roleOrder = Roleorder::where('roles_id', $role_id)->select('orderby')->first();
                $order_id = ($roleOrder === null && $roleSlug === 'administrator') ? 1 : $roleOrder->orderby;

                //현재 등록자 의 권한 바로 다음 선택
                $child_order = $order_id + 1;

                $next = Roleorder::where('orderby', $child_order)->select('roles_id')->first();
                $next_id = $next->roles_id;

                //바로 밑단계 권한 등록
                DB::table('admin_role_users')->insert([
                    'role_id' => $next_id,
                    'user_id' => $form->model()->id,
                ]);

                //users 테이블 name 컬럼 정보 업데이트
                $user_table = config('admin.database.users_table');
                $form->name = DB::table('admin_roles')->where('id', $next_id)->value('name');
                DB::table($user_table)->where('id', $form->model()->id)->update(['name' => $form->name]);

                //본사 부터는 추천 계정 등록
                $company = DB::table('admin_roles')->where('slug', 'company')->first();
                $companyOrder = Roleorder::where('roles_id', $company->id)->select('orderby')->first();


                //마스터가 생성 했다면
                if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master')) {
                    DB::table('recommends')->insert([
                        'recommend_id' => 0,
                        'user_id' => $form->model()->id,
                        'step1_id' => $form->model()->id,
                    ]);
                }

                //본사라면
                if ($order_id === $companyOrder->orderby) {
                    DB::table('recommends')->insert([
                        'recommend_id' => Admin::user()->id,
                        'user_id' => $form->model()->id,
                        'step1_id' => Admin::user()->id,
                        'step2_id' => $form->model()->id,
                    ]);
                }

                //본사보다 하위라면
                if ($order_id > $companyOrder->orderby) {

                    $step = DB::table('recommends')->where('user_id', Admin::user()->id)->first();

                    $step1_id = $step->step1_id;
                    $step2_id = $step->step2_id;
                    $step3_id = $step->step3_id;
                    $step4_id = $step->step4_id;
                    $step5_id = $step->step5_id;

                    $isGot = false;
                    if ($step->step3_id === null) {
                        $isGot = true;
                        $step3_id = $form->model()->id;
                    }
                    if ($isGot === false && $step->step4_id === null) {
                        $isGot = true;
                        $step4_id = $form->model()->id;
                    }
                    if ($isGot === false && $step->step5_id === null) {
                        $step5_id = $form->model()->id;
                    }

                    DB::table('recommends')->insert([
                        'recommend_id' => Admin::user()->id,
                        'user_id' => $form->model()->id,
                        'step1_id' => $step1_id,
                        'step2_id' => $step2_id,
                        'step3_id' => $step3_id,
                        'step4_id' => $step4_id,
                        'step5_id' => $step5_id,
                    ]);
                }
            } else {
                /*GameAccount::where('AccountIDx', $form->model()->account_id)
                    ->update(['PassWord' => $form->model()->temp_password]);*/
            }
        });



        return $form;
    }
}
