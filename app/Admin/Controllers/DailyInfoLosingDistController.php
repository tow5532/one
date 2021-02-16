<?php

namespace App\Admin\Controllers;

use App\Commission;
use App\DailyInfoRolling;
use App\DailyInfoUserRollingResult;
use App\HeadquarterLog;
use App\Recommend;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class DailyInfoLosingDistController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '총판정산';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $userTable = 'users';

        $grid->model()->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id');

        if (Admin::user()->isRole('administrator') || Admin::user()->isRole('master')) {
            $grid->model()->whereNotNull('recommends.step3_id');
            $grid->model()->whereNull('recommends.step4_id');
            $grid->model()->whereNull('recommends.step5_id');
        } else {
            //로그인한 회원 권한 조회
            $admin_role         = Admin::user()->roles;
            $admin_role_id      = $admin_role[0]['id'];
            $admin_role_slug    = $admin_role[0]['slug'];
            $admin_order        = DB::table('admin_roles_order')->where('roles_id', $admin_role_id)->first();

            if ($admin_role_slug === 'distributor') {
                $grid->model()->where('recommends.step3_id', Admin::user()->id);
                $grid->model()->whereNull('recommends.step4_id');
                $grid->model()->whereNull('recommends.step5_id');
            } else {
                //상위 계정이라면
                if ($admin_order->orderby === 2){
                    $grid->model()->where('recommends.step1_id', Admin::user()->id);
                    $grid->model()->whereNotNull('recommends.step2_id');
                    $grid->model()->whereNotNull('recommends.step3_id');
                    $grid->model()->whereNull('recommends.step4_id');
                    $grid->model()->whereNull('recommends.step5_id');
                }
                elseif ($admin_order->orderby === 3){
                    $grid->model()->where('recommends.step2_id', Admin::user()->id);
                    $grid->model()->whereNotNull('recommends.step3_id');
                    $grid->model()->whereNull('recommends.step4_id');
                    $grid->model()->whereNull('recommends.step5_id');
                }
                elseif ($admin_order->orderby === 4){
                    $grid->model()->where('recommends.step3_id', Admin::user()->id);
                    $grid->model()->whereNull('recommends.step4_id');
                    $grid->model()->whereNull('recommends.step5_id');
                }
            }
        }

        $grid->model()->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id');

        $grid->column('등급')->display(function (){
            $user_grade = DB::table('admin_role_users')->where('user_id', $this->id)->first();
            return DB::table('admin_roles')->where('id', $user_grade->role_id)->value('name');
        });

        $grid->column('username', '아이디');

        /*$grid->column('루징수수료')->display(function (){
            $company_commission     = Commission::where('user_id', $this->id)->first();
            $com_losing_per         = $company_commission->losing;

            //바로 밑에 하부 루징률 조회
            $recommend          = Recommend::where('user_id', $this->id)->first();
            $sub_commission     = Commission::where('user_id', $recommend->step2_id)->first();

            return $com_losing_per - $sub_commission->losing;
        });*/

        $grid->column('보유알')->display(function (){
            //현재 보유 알 합계
            $add_cnt    = HeadquarterLog::where('user_id', $this->id)->where('use_point', '=', '0')->sum('point');
            $minus_cnt  = HeadquarterLog::where('user_id', $this->id)->where('point', '=', '0')->sum('use_point');
            $in_point   = $add_cnt - $minus_cnt;
            return number_format($in_point);
        });

        $grid->column('losing_profit', '루징수수료');

        $grid->column('rolling_profit', '롤링수수료')->display(function ($rolling_profit){
            return $rolling_profit;
        });

        $grid->column('하부갯수')->display(function (){
            $cnt = Recommend::where('step3_id', $this->id)->count();
            return $cnt;
        });


        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            //$actions->disableView();
        });
        //$grid->disableFilter();
        //$grid->disableActions();
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
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
        $show = new Show(User::findOrFail($id));

        //############ 권한 조회 #############################
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


        $show->field('등급')->as(function (){
            $user_grade = DB::table('admin_role_users')->where('user_id', $this->id)->first();
            return DB::table('admin_roles')->where('id', $user_grade->role_id)->value('name');
        });
        $show->field('username', '아이디');
        $show->field('losing_profit', '루징수수료율');
        $show->field('rolling_profit', '롤링수수료율');

        $show->field('베팅금액')->as(function (){
            $result = DailyInfoUserRollingResult::where('user_id', $this->id)->orderBy('id', 'desc')->first();

            return ($result === null) ? 0 : number_format($result->total_betting);
        });

        $show->field('롤링전환누적수익')->as(function (){
            return number_format(HeadquarterLog::where('user_id', $this->id)
                ->where('use_point', '=', '0')
                ->where('po_content', 'change_rolling')
                ->sum('point'));
        });

        //본인 꺼만 알로 교환 할수 있게 해야한다
        if ((int)$id === (int)Admin::user()->id) {
            $show->field('admin_rolling', '롤링 수익')->unescape()->as(function ($admin_rolling) {
                return '<span><b>' . number_format($admin_rolling) . '</b></span><span style="margin-left: 15px;"><input type="button" id="money_chg" value="롤링수익 머니교환"></span>';
            });
            Admin::script('
            $("#money_chg").click(function(){
                if(confirm("전환하면 취소되지 않습니다. 정말 해당 롤링 수익을 알로 전환 하시겠습니까?")){
                   $.ajaxSetup({
                        headers: {
                            "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr("content")
                        }
                   });
                   $.ajax({
                        url: "/sales/change/rolling",
                        type: "POST",
                        dataType: "json",
                        async: false,
                        beforeSend: function () {
                        },
                        success: function (json) {
                            if (json.success ==  true) {
                                alert(json.err_msg);
                                document.location.reload();
                            }
                            else {
                            alert(json.err_msg);
                            document.location.reload();
                            }
                        },
                        complete: function () {
                        },
                        error: function (request, status, error) {
                            alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        }
                    });
                }
            });
            ');
        }
        else {
            $show->field('admin_rolling', '롤링 수익')->unescape()->as(function ($admin_rolling) {
                return  number_format($admin_rolling);
            });
        }

        $show->headquarters('롤링 수익 변환 내역', function ($headquarters){
            $headquarters->model()->where('po_content', 'change_rolling');
            $headquarters->model()->orderBy('id', 'desc');

            $headquarters->point('수량');

            $headquarters->created_at('등록일자');

            //page options
            $headquarters->disableCreateButton();
            $headquarters->disableFilter();
            $headquarters->disableActions();
            $headquarters->disableExport();
        });

        $show->losingTotalCompany('루징내역', function ($losingTotalCompany){

            $losingTotalCompany->search_date('정산일자');

            $losingTotalCompany->total_past_point('이월된 유저 보유 칩')->display(function ($total_past_point){
                return number_format($total_past_point);
            });
            /*$losingTotalCompany->total_point('총 보유 포인트')->display(function ($total_point){
                return number_format($total_point);
            });*/
            $losingTotalCompany->total_deposit('충전된 유저 칩')->display(function ($total_deposit){
                return number_format($total_deposit);
            });
            $losingTotalCompany->total_refund('환전한 유저칩')->display(function ($total_refund){
                return number_format($total_refund);
            });
            /*$losingTotalCompany->total_term_point('현재 보유 유저칩')->display(function ($total_term_point){
                return number_format($total_term_point);
            });*/
            $losingTotalCompany->total_point('현재 보유 유저칩')->display(function ($total_point) {
                return number_format($total_point);
            });

            $losingTotalCompany->total_game_money('현재 보유 게임머니')->display(function ($total_game_money){
                return number_format($total_game_money);
            });

            $losingTotalCompany->user_losing_total('전체 회원 루징')->display(function ($user_losing_total) {
                return number_format($user_losing_total);
            });

            $losingTotalCompany->total_losing('마이루징')->display(function ($total_losing){
                return number_format($total_losing);
            });
            $losingTotalCompany->total_losing_revenue('루징수익')->display(function ($total_losing_revenue){
                return number_format($total_losing_revenue);
            });

            $losingTotalCompany->footer(function ($query){
                //return "<div style='padding: 10px;'>루징수익 총액 ： $data</div>";
                return '<div style="margin-left: 15px; text-align: right; padding: 10px;"><input type="button" id="losing_chg" value="루징수익 머니교환"></div>' ;
            });

            Admin::script('
            $("#losing_chg").click(function(){
                if(confirm("전환하면 취소되지 않습니다. 정말 루징 수익을 알로 전환 하시겠습니까?")){
                   $.ajaxSetup({
                        headers: {
                            "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr("content")
                        }
                   });
                   $.ajax({
                        url: "/sales/change/losing",
                        type: "POST",
                        dataType: "json",
                        async: false,
                        beforeSend: function () {
                        },
                        success: function (json) {
                            if (json.success ==  true) {
                                alert(json.err_msg);
                                document.location.reload();
                            }
                            else {
                            alert(json.err_msg);
                            document.location.reload();
                            }
                        },
                        complete: function () {
                        },
                        error: function (request, status, error) {
                            alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
                        }
                    });
                }
            });
            ');


            //실시간
            /*
            $losingTotalCompany->header(function ($query) {
               //현재날짜
               $todayDate = Carbon::today()->toDateString();
               $yesterDate = Carbon::yesterday()->toDateString();

               $list = $query->select('user_id')->first();

               //현재 회원 시퀀스의  등급을 조회한다.
                $user_grade     = DB::table('admin_role_users')->where('user_id', $list->user_id)->first();
                $user_role      = DB::table('admin_roles')->where('id', $user_grade->role_id)->first();

                $where_query = '';
                if ($user_role->slug === 'company'){
                    $where_query = 'recommends.step1_id';
                }
                elseif ($user_role->slug === 'sub_company'){
                    $where_query = 'recommends.step2_id';
                }
                elseif ($user_role->slug === 'distributor'){
                    $where_query = 'recommends.step3_id';
                }
                elseif ($user_role->slug === 'store'){
                    $where_query = 'recommends.step4_id';
                }


                //하위 일반 회원들 조회
                $lows = DB::table('users')
                    ->join('recommends', 'users.id', '=', 'recommends.user_id')
                    ->where($where_query, '=', $list->user_id)
                    ->whereNotNull('recommends.step2_id')
                    ->whereNotNull('recommends.step3_id')
                    ->whereNotNull('recommends.step4_id')
                    ->whereNotNull('recommends.step5_id')
                    ->select('users.*')->get();
                $userArray = array();
                foreach ($lows as $low) {
                    array_push($userArray, $low->id);
                }


                //유저 환전 총액
                $refund_step        = RefundStep::where('code', 'refund_ok')->first();
                $user_refund_total  = Refund::whereDate('updated_at', '=', $todayDate)
                    ->where('step_id', $refund_step->id)
                    ->whereIn('user_id', $userArray)
                    ->sum('amount');

                //유저 포인트 총합
                $add_cnt        = Point::whereIn('user_id', $userArray)->where('use_point', '=', '0')->sum('point');
                $minus_cnt      = Point::whereIn('user_id', $userArray)->where('point', '=', '0')->sum('use_point');
                $user_point     = $add_cnt - $minus_cnt;

                //유저 정산 기간 포인트 총합
                $user_term_point = Point::whereIn('user_id', $userArray)
                    ->where('use_point', '=', '0')
                    ->whereDate('created_at', '=', $todayDate)
                    ->sum('point');

                //유저의 이전 정산시 찍힌 보유 포인트 를 가져온다
                $past_losing_data   = DailyInfoLosingTotal::where('user_id' , $list->user_id)->where('search_date', $yesterDate)->first();
                $past_user_point    = ($past_losing_data === null) ? 0 : $past_losing_data->total_past_point;

                //해당 유저 루징금액 계산
                // 루징금액 = (기간동안 충전된 포인트 + 이전 정산시 찍힌 보유 포인트 ) - 기간 출금 승인된 포인트 -total_past_point 현재 보유 포인트
                $user_losing_amount = ($user_term_point + $past_user_point) - $user_refund_total - $user_point;



                $headers = [
                    '정산기간',
                    '이월된 유저 보유 칩',
                    '충전된 유저 칩',
                    '환전한 유저칩',
                    '현재 보유 유저칩',
                    '루징칩',
                    '루징수익금'
                ];
                $rows = [
                    [
                        '현재',
                        $past_user_point,
                        $user_term_point,
                        $user_refund_total,
                        $user_point,
                        '루징칩',
                        '루징수익금',
                    ]
                ];
                $table = new Table($headers, $rows);

                return $table->render();
            });
            */

            //page options
            $losingTotalCompany->disableCreateButton();
            $losingTotalCompany->disableFilter();
            $losingTotalCompany->disableActions();
            $losingTotalCompany->disableExport();

        });

        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                // $tools->disableList();
                $tools->disableDelete();
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

    }
}
