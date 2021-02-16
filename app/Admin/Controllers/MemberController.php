<?php

namespace App\Admin\Controllers;

use App\Commission;
use App\GameAccount;
use App\ProfitChange;
use App\Recommend;
use App\SlotGameAuth;
use Encore\Admin\Widgets\Table;
use App\Roleorder;
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
use Illuminate\Support\Str;

class MemberController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '하부';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */

    protected function grid()
    {
        //$grid = new Grid(new User());
        /*$grid->model()
            ->join('admin_role_users', 'users.id', '=', 'admin_role_users.user_id')
            ->join('admin_roles', 'admin_role_users.role_id', '=', 'admin_roles.id')
            ->join('admin_roles_order', 'admin_roles.id' , '=', 'admin_roles_order.roles_id')
            ->select('users.*', 'admin_role_users.role_id', 'admin_roles.name', 'admin_roles.slug')
            ->orderBy('users.id', 'desc');*/

        $userTable = config('admin.database.users_table');
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel());

        $grid->model()->join('recommends', $userTable.'.'.  'id', '=', 'recommends.user_id');


        //어드민 계정이 아닐경우 조건문 을 추가 한다.
        if (!Admin::user()->isRole('administrator') && !Admin::user()->isRole('master')) {

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
            $grid->model()->where('recommends.' . $step_col, Admin::user()->id);
        }

        //$grid->model()->where('admin_yn', '=', 'N');

        $grid->model()->select('users.*', 'recommends.recommend_id');

        $grid->model()->orderBy('id', 'desc');


        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            //$actions->disableView();
        });

       /* $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('username', trans('admin.member.user_id'));
            $options = [
            ];
            //$filter->between('created_at', '등록일')->datetime($options);
            $filter->expand();
        });
       */

        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->column('id', 'No');

        $grid->column('roles', trans('admin.member.level'))->pluck('name')->label();

        $grid->column('username', trans('admin.member.user_id'));

        //$grid->column('profit', trans('admin.member.profit'));

        //$grid->column('losing_profit', '루징 수수료율');

       $grid->recommend_id(trans('admin.member.referrer'))->display(function ($recommend_id){
            return User::where('id', $recommend_id)->value('username');
       });

        $grid->created_at(trans('admin.member.created_at'))->display(function ($created_at){
            return Carbon::parse($created_at)->timezone('Asia/Seoul')->toDateTimeString();
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

        //마스터나 어드민 계정이 아닐 경우는 해당 아이디의 추천정보 조회
        if (!Admin::user()->inRoles(['administrator', 'master'])){

            $recommend  = DB::table('recommends')->where('user_id', $id)->first();

            $user_step = 0;

            if ($recommend->step1_id === (int)$id){
                $user_step = 1;
            }
            if ($recommend->step2_id === (int)$id){
                $user_step = 2;
            }
            if ($recommend->step3_id === (int)$id){
                $user_step = 3;
            }
            if ($recommend->step4_id === (int)$id){
                $user_step = 4;
            }
            if ($recommend->step5_id === (int)$id){
                $user_step = 5;
            }

            //접속한 회원 이 해당 정보 열람 가능한지 조회
            //1레벨이면 자기이외는 볼수가 없음
            if ($user_step === 1 && Admin::user()->id !== (int)$id ){
                $error = new MessageBag([
                    'title'   => '잘못된 접근 입니다.',
                    'message' => '해당 웹페이지를 접근할 권한이 없습니다.',
                ]);
                return back()->with(compact('error'));
            }

            //스텝이 1이 아니면 자기 상위만 볼수 있음
            if ($user_step > 1 && Admin::user()->id !== (int)$id){
                $step_cnt = 0;

                for ($i = $user_step; $i > 0; $i--){
                    $user_step_coulum = 'step'.$i.'_id';
                    $recon_user = DB::table('recommends')
                        ->where('user_id', $id)
                        ->where($user_step_coulum , Admin::user()->id)->first();
                    if ($recon_user){
                        $step_cnt++;
                    }
                }

                if ($step_cnt === 0){
                    $error = new MessageBag([
                        'title'   => '잘못된 접근 입니다.',
                        'message' => '해당 웹페이지를 접근할 권한이 없습니다.',
                    ]);
                    return back()->with(compact('error'));
                }
            }

            if($user_step === 0){
                $error = new MessageBag([
                    'title'   => '잘못된 접근 입니다.',
                    'message' => '해당 웹페이지를 접근할 권한이 없습니다.',
                ]);
                return back()->with(compact('error'));
            }
        }


        $show->panel()
            ->tools(function ($tools) {
                //$tools->disableEdit();
               // $tools->disableList();
                $tools->disableDelete();
            });

        /*$show->field('roles', trans('admin.member.level'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();*/

        $show->field('username', trans('admin.member.user_id'));

        //해당회원 등급 조회
        $user = DB::table('admin_role_users')->where('user_id', $id)->first();
        $admin_role_slug = DB::table('admin_roles')->where('id', $user->role_id)->value('slug');

        //일반 회원만 노출
        if ($admin_role_slug === 'user') {
            $show->field('withdrawal_password', trans('admin.member.withdrawal'));
            $show->field('bank', trans('admin.member.bank'));
            $show->field('account', trans('admin.member.account'));
            $show->field('holder', trans('admin.member.holder'));
        }

        //일반 회원이 아닐경우만 수수료 노출
        if ($admin_role_slug !== 'user') {
            $show->field('rolling_profit', '롤링 수수료율');
            $show->field('losing_profit', '루징 수수료율');
        }

        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        $show->divider();


        $show->field(trans('admin.member.belong'))->unescape()->as(function (){

            //로그인한 회원 권한 조회
            $admin_role         = Admin::user()->roles;
            $admin_role_id      = $admin_role[0]['id'];
            $admin_role_slug    = $admin_role[0]['slug'];

            $admin_order        = DB::table('admin_roles_order')->where('roles_id', $admin_role_id)->first();

            $admin_order_num    = ($admin_role_slug === 'administrator' || $admin_role_slug === 'master') ? 1 : $admin_order->orderby;

            $recommend      = DB::table('recommends')->where('user_id', $this->id)->first();

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

        $show->field(trans('admin.member.referrer'))->as(function (){
            $recommend = DB::table('recommends')->where('user_id', $this->id)->first();
            if ($recommend->recommend_id === null || $recommend->recommend_id === 0){
                return '';
            } else {
                $user = User::find($recommend->recommend_id);
                return $user->username;
            }
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @param $id
     * @param Content $content
     * @return void
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form($id)->edit($id));
    }


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

        if ($form->isEditing()){

            $this_user = User::find($id);

            if ($this_user === null){
                $error = new MessageBag([
                    'title'   => '잘못된 접근 입니다.',
                    'message' => '없는 회원 정보 입니다.',
                ]);
                return back()->with(compact('error'));
            }


            //마스터나 어드민 계정이 아닐 경우는 해당 아이디의 추천정보 조회
            //마스터나 어드민 계정이 아닐 경우는 해당 아이디의 추천정보 조회
            if (!Admin::user()->inRoles(['administrator', 'master'])){

                $recommend  = DB::table('recommends')->where('user_id', $id)->first();

                $user_step = 0;

                if ($recommend->step1_id === (int)$id){
                    $user_step = 1;
                }
                if ($recommend->step2_id === (int)$id){
                    $user_step = 2;
                }
                if ($recommend->step3_id === (int)$id){
                    $user_step = 3;
                }
                if ($recommend->step4_id === (int)$id){
                    $user_step = 4;
                }
                if ($recommend->step5_id === (int)$id){
                    $user_step = 5;
                }

                //접속한 회원 이 해당 정보 열람 가능한지 조회
                //1레벨이면 자기이외는 볼수가 없음
                if ($user_step === 1 && Admin::user()->id !== (int)$id ){
                    $error = new MessageBag([
                        'title'   => '잘못된 접근 입니다.',
                        'message' => '해당 웹페이지를 접근할 권한이 없습니다.',
                    ]);
                    return back()->with(compact('error'));
                }

                //스텝이 1이 아니면 자기 상위만 볼수 있음
                if ($user_step > 1 && Admin::user()->id !== (int)$id){
                    $step_cnt = 0;

                    for ($i = $user_step; $i > 0; $i--){
                        $user_step_coulum = 'step'.$i.'_id';
                        $recon_user = DB::table('recommends')
                            ->where('user_id', $id)
                            ->where($user_step_coulum , Admin::user()->id)->first();
                        if ($recon_user){
                            $step_cnt++;
                        }
                    }

                    if ($step_cnt === 0){
                        $error = new MessageBag([
                            'title'   => '잘못된 접근 입니다.',
                            'message' => '해당 웹페이지를 접근할 권한이 없습니다.',
                        ]);
                        return back()->with(compact('error'));
                    }
                }

                if($user_step === 0){
                    $error = new MessageBag([
                        'title'   => '잘못된 접근 입니다.',
                        'message' => '해당 웹페이지를 접근할 권한이 없습니다.',
                    ]);
                    return back()->with(compact('error'));
                }
            }
        }


        // $form->setTitle($roles[0]['name']. trans('admin.member.sub_title'));

        /*if ($form->isCreating()) {
            $form->divider('등급 설정');

            $form->select('company', '본사')->options(function () {
                $users = Recommend::with('user')
                    ->whereNotNull('step1_id')
                    ->whereNull('step2_id')
                    ->get();
                $list = array();
                foreach ($users as $user) {
                    $list[$user->user->id] = $user->user->username;
                }
                return $list;
            })->load('sub_company', '/api/select-sub-company')->required();
            $form->select('sub_company', '부본')
                ->load('distributor', '/api/select-distributor');
            $form->select('distributor', '총판')
                ->load('store', '/api/select-store');
            $form->select('store', '매장');
        }*/


        $form->divider('회원정보');

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

            if ($form->isCreating()) {
                //기본 기준 값
                $losing_max             = 100;
                $rolling_max            = 5;
                $losing_display_max     = 100;
                $rolling_display_max    = 5;

                if (!Admin::user()->inRoles(['administrator', 'master'])) {
                    $above              = rightAboveRecommend(Admin::user()->id);
                    $losing_max         = (int)$above->losing_profit;
                    $losing_display_max = $losing_max;

                    $rolling_max            = $above->rolling_profit;
                    $rolling_display_max    = $rolling_max;
                }

                if (Admin::user()->isRole('store')) {
                    $form->hidden('losing_profit')->default('0');
                    $form->hidden('rolling_profit')->default('0');
                }
                else {
                    $form->divider('수수료설정');
                    $form->number('losing_profit', '루징 수수료')
                        ->max($losing_max)
                        ->required()
                        ->help('  (Max : ' . $losing_display_max . ')');

                    $form->decimal('rolling_profit', '롤링 수수료')
                        ->options(['max' => $rolling_max, 'min' => 0, 'digits' => 1])
                        ->required()
                        ->help('(Max : ' . $rolling_display_max . ')');
                }
            }

            if ($form->isEditing()) {
                $form->display('losing_profit', '루징 수수료율');
                $form->display('rolling_profit', '롤링 수수료율');
            }
        }

        $form->divider('금융정보');

        $form->text('bank', trans('admin.member.bank'))->rules('required');

        $form->text('account', trans('admin.member.account'))->rules('required');

        $form->text('holder', trans('admin.member.holder'))->rules('required');

        $form->text('phone', trans('admin.member.phone'))->rules('required');

        //일반회원 생성시만 등록가능하게 한다.
        if ($admin_role_slug === 'user' || Admin::user()->isRole('store')) {
            $form->divider('기타정보');

            $form->text('withdrawal_password', trans('admin.member.withdrawal'))->rules([
                'max:4', 'required'
            ]);
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

        if (Admin::user()->isRole('store')){
            $form->hidden('is_store_user')->default('Y');
        }

        $form->ignore('password_confirm');


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

            /*if ($form->isCreating()) {
                $form->ignore('company');
                $form->ignore('sub_company');
                $form->ignore('distributor');
                $form->ignore('store');

                $step_id = $form->compnay;
                $step2_id = ($form->sub_company === '0') ? null : $form->sub_company;
                $step3_id = ($form->distributor === '0') ? null : $form->distributor;
                $step4_id = ($form->store === '0') ? null : $form->store;

                Recommend::create([

                ]);
            }*/

            //등록시만 작동하게
            if ($form->isCreating()) {
                //activated 1
                $userTable = config('admin.database.users_table');
                DB::table($userTable)->where('id', $form->model()->id)
                    ->update([
                        'activated' => '1',
                        'temp_password' => $form->model()->temp_password,
                    ]);

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
                //$form->name = DB::table('admin_roles')->where('id', $next_id)->value('name');
                $form->name = $form->model()->username;
                DB::table($user_table)->where('id', $form->model()->id)->update(['name' => $form->name]);

                //본사 부터는 추천 계정 등록
                $company = DB::table('admin_roles')->where('slug', 'company')->first();
                $companyOrder = Roleorder::where('roles_id', $company->id)->select('orderby')->first();


                //마스터가 생성 했다면
                if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master')) {
                    $recommend = new Recommend;
                    $recommend->recommend_id = 0;
                    $recommend->user_id = $form->model()->id;
                    $recommend->step1_id = $form->model()->id;
                    $recommend->save();

                    //본사 계정이라면 수수료율 설정 테이블에 등록
                    $commission = new Commission;
                    $commission->user_id = $form->model()->id;
                    $commission->rolling = $form->model()->rolling_profit;
                    $commission->losing = $form->model()->losing_profit;
                    $commission->company_yn = 'Y';
                    $commission->save();
                }

                //본사라면
                if ($order_id === $companyOrder->orderby) {
                    $recommend = new Recommend;
                    $recommend->recommend_id = Admin::user()->id;
                    $recommend->user_id = $form->model()->id;
                    $recommend->step1_id = Admin::user()->id;
                    $recommend->step2_id = $form->model()->id;
                    $recommend->save();

                    //수수료 테이블에 등록
                    $commission = new Commission;
                    $commission->user_id = $form->model()->id;
                    $commission->rolling = $form->model()->rolling_profit;
                    $commission->losing = $form->model()->losing_profit;
                    $commission->save();
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

                    $recommend = new Recommend;
                    $recommend->recommend_id = Admin::user()->id;
                    $recommend->user_id = $form->model()->id;
                    $recommend->step1_id = $step1_id;
                    $recommend->step2_id = $step2_id;
                    $recommend->step3_id = $step3_id;
                    $recommend->step4_id = $step4_id;
                    $recommend->step5_id = $step5_id;
                    $recommend->save();

                    //수수료 테이블에 등록
                    $commission = new Commission;
                    $commission->user_id = $form->model()->id;
                    $commission->rolling = $form->model()->rolling_profit;
                    $commission->losing = $form->model()->losing_profit;
                    $commission->save();

                    //매장 계정이 생성 했다면, 게임DB에 계정정보 등록
                    if (Admin::user()->isRole('store')){
                        $session_id = Str::random(80) . '' .  $form->model()->id;
                        $auth = new SlotGameAuth;
                        $auth->CertificationKey = $session_id;
                        $auth->UpdateDate = Carbon::now();
                        $auth->save();

                        //회원 정보에 게임 시퀀스 업데이트
                        $user = User::find($form->model()->id);
                        $user->account_id = $auth->Aid;
                        $user->save();
                    }
                }
            } else {
                /*GameAccount::where('AccountIDx', $form->model()->account_id)
                    ->update(['PassWord' => $form->model()->temp_password]);*/
            }
        });


        return $form;
    }
}
