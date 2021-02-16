<?php

namespace App\Admin\Controllers;

use App\DailyinfoCompany;
use App\Deposit;
use App\DepositStep;
use App\GameInfo;
use App\GameMember;
use App\GameTourRegist;
use App\HeadquarterLog;
use App\HouseEdge;
use App\LogMoney;
use App\Point;
use App\Refund;
use App\RefundStep;
use App\Tcommand;
use App\TSafer;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class RealTimeCompanyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Today Sales (00:00 ~ current)';

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

        $grid->disableCreateButton();
        //$grid->disableFilter();
        $grid->disableActions();
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });


        $grid->model()->join('recommends', $userTable . '.' . 'id', '=', 'recommends.user_id');
        $grid->model()->select($userTable . '.' . '*', 'recommends.user_id', 'recommends.recommend_id');
        $grid->model()->whereNotNull('recommends.step1_id');
        $grid->model()->whereNull('recommends.step2_id');
        $grid->model()->whereNull('recommends.step3_id');
        $grid->model()->whereNull('recommends.step4_id');
        $grid->model()->whereNull('recommends.step5_id');

        //$grid->column('id', 'No');

        //$grid->column('roles', trans('admin.member.level'))->pluck('name')->label();

        $grid->column('username', trans('admin.member.user_id'));

        $grid->column(trans('admin.realtime_sales.total_user_payment'))->display(function (){
            $step = DepositStep::where('code', 'success')->first();
            return number_format(Deposit::whereDate('updated_at', '=', Carbon::today()->toDateString())
                ->where('step_id', $step->id)
                ->sum('charge_amount'));
        });

        $grid->column(trans('admin.realtime_sales.total_user_exchange'))->display(function (){
            $refund_step = RefundStep::where('code', 'refund_ok')->first();
            return number_format(Refund::whereDate('updated_at', '=', Carbon::today()->toDateString())
                ->where('step_id', $refund_step->id)
                ->sum('amount'));
        });

        $grid->column(trans('admin.sale_company.total_user_chips'))->display(function (){
            //본사 하위 회원 시퀀스 조회
            $user_table = config('admin.database.users_table');
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->whereNotNull($user_table. '.account_id')
                ->where('recommends.step1_id', '=', $this->id)
                ->select('users.account_id')->get();
            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->account_id);
            }

            //유저 보유 게임칩 총 갯수
            $game_chips = GameMember::whereIn('AccountUniqueid', $userArray)
                ->select(DB::raw('sum(convert(bigint,(convert(decimal(38), Have_Money) / 100000000))) as chips'))
                ->get();
            $game_total_chips = 0;
            foreach ($game_chips as $game_chip){
                $game_total_chips = $game_chip->chips;
            }

            //게임머니에서 포인트로 변화할때 신청했지만, 금고에 존재하는 것 조회 및 합산
            $t_safer = TSafer::whereIn('AccountuniqueID', $userArray)->where('flag', '0')->sum('safe_money');
            $game_total_chips += $t_safer;

            //토너먼트 예약 머니 총합 조회후 합산
            $tnmt_regist = GameTourRegist::whereIn('AccountUniqueID', $userArray)->sum('buyin_money');
            $game_total_chips += $tnmt_regist;

            return number_format($game_total_chips);
        });

        $grid->column('not_col_user_sun', trans('admin.realtime_sales.user_sun_point'))->display(function (){
            $add_cnt    = Point::where('use_point', '0')->sum('point');
            $minus_cnt  = Point::where('point', '0')->sum('use_point');
            $user_point = $add_cnt - $minus_cnt;

            //추가로 출금 신청 시 승인 안난것들 조회후 합산
            $refund_step = RefundStep::where('code', 'refund')->first();
            $refund_point = Refund::where('step_id', $refund_step->id)->sum('amount');
            $user_point += $refund_point;

            //게임 머니로 변환 신청 했으나 아직 금고 에 있는 포인트 조회 후 합산
            $t_command_point = Tcommand::all()->sum('val1');
            $user_point += $t_command_point;

            return number_format($user_point);
        });

        $grid->column('not_col', trans('admin.realtime_sales.user_bonus_cnt'))->display(function (){
            //관리자나 무료로 받은 포인트 합계 조회
            $free_cnt = Point::whereIn('po_content', ['join_event', 'admin_charge'])
                ->whereDate('created_at', '=', Carbon::today()->toDateString())->sum('point');
            return number_format($free_cnt);
        });


       /* $grid->column(trans('admin.sale_company.total'))->display(function (){
            //$normal_company_per   = 43.75;
            $normal_company_per   = 100;
            $sit_company_per      = 100;
            $tour_company_per     = 100;

            //게임머니 <-> 포인트 변활 비율
            $game_info  = GameInfo::where('code', 'holdem')->first();
            $outQuote   = (int)$game_info->outquote;

            //본사 하위 회원 시퀀스 조회
            $user_table = config('admin.database.users_table');
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->whereNotNull($user_table. '.account_id')
                ->where('recommends.step1_id', '=', $this->id)
                ->select('users.account_id')->get();

            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->account_id);
            }

            //일반게임
            $houses = HouseEdge::where('channel', '=', 'normal')
                ->whereDate('log_date', '=', Carbon::today()->toDateString())
                ->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            $normal_company = $sum * $normal_company_per / 100;
            $normal_company_point = floor($normal_company / $outQuote);


            //싯앤고게임
            $houses = HouseEdge::where('channel', '=',  'sitngo')
                ->whereDate('log_date', '=', Carbon::today()->toDateString())
                //->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            $sit_company = $sum * $sit_company_per / 100;
            $sit_company_point = floor($sit_company / $outQuote);

            //토너먼트게임
            $houses = HouseEdge::where('channel', '=', 'tournament')
                ->whereDate('log_date', '=', Carbon::today()->toDateString())
                //->whereIn('AccountUniqueID', $userArray)
                ->get();
            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            $tour_company = $sum * $tour_company_per / 100;
            $tour_company_point = floor($tour_company / $outQuote);


            //본사 구입하고 남음 칩 갯수
            $add_cnt        = HeadquarterLog::where('use_point', '=', '0')->sum('point');
            $minus_cnt      = HeadquarterLog::where('point', '=', '0')->sum('use_point');
            $payed_point    = $add_cnt - $minus_cnt;
            $re_sale_cnt    = $normal_company_point + $sit_company_point + $tour_company_point;

            return number_format($payed_point + $re_sale_cnt);
        });
       */

        /*$grid->column(trans('admin.sale_company.payment'))->display(function (){
            //본사 구입하고 남음 칩 갯수
            $add_cnt        = HeadquarterLog::whereDate('created_at', '=', Carbon::today()->toDateString())
                ->where('po_content' , '<>', 'daily_resale')->where('use_point', '=', '0')->sum('point');
            $minus_cnt      = HeadquarterLog::whereDate('created_at', '=', Carbon::today()->toDateString())->where('point', '=', '0')->sum('use_point');
            return $add_cnt - $minus_cnt;
        });*/

        /*$grid->column(trans('admin.realtime_sales.profit'))->display(function (){
            //$normal_company_per   = 43.75;
            $normal_company_per   = 100;
            $sit_company_per      = 100;
            $tour_company_per     = 100;

            //게임머니 <-> 포인트 변활 비율
            $game_info  = GameInfo::where('code', 'holdem')->first();
            $outQuote   = (int)$game_info->outquote;

            //본사 하위 회원 시퀀스 조회
            $user_table = config('admin.database.users_table');
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->whereNotNull($user_table. '.account_id')
                ->where('recommends.step1_id', '=', $this->id)
                ->select('users.account_id')->get();

            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->account_id);
            }

            //일반게임
            $houses = HouseEdge::where('channel', '=', 'normal')
                ->whereDate('log_date', '=', Carbon::today()->toDateString())
                ->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            $normal_company = $sum * $normal_company_per / 100;
            $normal_company_point = floor($normal_company / $outQuote);

            //싯앤고게임
            $houses = HouseEdge::where('channel', '=',  'sitngo')
                ->whereDate('log_date', '=', Carbon::today()->toDateString())
                //->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            $sit_company = $sum * $sit_company_per / 100;
            $sit_company_point = floor($sit_company / $outQuote);

            //토너먼트게임
            $houses = HouseEdge::where('channel', '=', 'tournament')
                ->whereDate('log_date', '=', Carbon::today()->toDateString())
                //->whereIn('AccountUniqueID', $userArray)
                ->get();
            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            $tour_company = $sum * $tour_company_per / 100;
            $tour_company_point = floor($tour_company / $outQuote);

            $re_sale_cnt    = $normal_company_point + $sit_company_point + $tour_company_point;
            return number_format($re_sale_cnt);
        });*/

        $grid->column(trans('admin.realtime_sales.total_fee'))->display(function (){
            $today                  = Carbon::today();
            $todayDate              = $today->toDateString();
            $normal_master_per      = 38;
            $normal_company_per     = 60;
            $normal_jackpot_per     = 2;

            $sit_master_per         = 40;
            $sit_company_per        = 60;

            $tour_master_per        = 40;
            $tour_company_per       = 60;

            $saleFee                = 0.23;

            //게임머니 <-> 포인트 변활 비율
            $game_info  = GameInfo::where('code', 'holdem')->first();
            $inQuote    = (int)$game_info->inquote;
            $outQuote   = (int)$game_info->outquote;

            //본사 하위 회원 시퀀스 조회
            $user_table = config('admin.database.users_table');
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->whereNotNull($user_table. '.account_id')
                ->where('recommends.step1_id', '=', $this->id)
                ->select('users.account_id')->get();

            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->account_id);
            }

            //일반게임
            $houses = HouseEdge::where('channel', '=', 'normal')
                ->whereDate('log_date', '=', $todayDate)
                ->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $normal_master  = $sum * $normal_master_per / 100;
            $normal_company = $sum * $normal_company_per / 100;
            $normal_jackopt = $sum * $normal_jackpot_per / 100;

            //$outQuote
            $normal_master_point  = floor($normal_master / $outQuote);
            $normal_company_point = floor($normal_company / $outQuote);
            $normal_jackopt_point = floor($normal_jackopt / $outQuote);

            //싯앤고게임
            $houses = HouseEdge::where('channel', '=',  'sitngo')
                ->whereDate('log_date', '=', $todayDate)
                //->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $sit_master  = $sum * $sit_master_per / 100;
            $sit_company = $sum * $sit_company_per / 100;

            $sit_master_point  = floor($sit_master / $outQuote);
            $sit_company_point = floor($sit_company / $outQuote);

            //토너먼트게임
            $houses = HouseEdge::where('channel', '=', 'tournament')
                ->whereDate('log_date', '=', $todayDate)
                //->whereIn('AccountUniqueID', $userArray)
                ->get();
            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $tour_master  = $sum * $tour_master_per / 100;
            $tour_company = $sum * $tour_company_per / 100;

            $tour_master_point  = floor($tour_master / $outQuote);
            $tour_company_point = floor($tour_company / $outQuote);

            //마스터 수수료 합 (노멀 + 토너먼트 + 샛인고)
            $master_total_fee = $normal_master_point + $tour_master_point + $sit_master_point;

            //본사 수수료 합 (노멀 + 토너먼트 + 싯앤고 )
            $company_total_fee = $normal_company_point + $tour_company_point + $sit_company_point;

            return number_format($normal_jackopt_point + $company_total_fee + $master_total_fee);
        });

        $grid->column(trans('admin.realtime_sales.jackpot'))->display(function (){
            $today                  = Carbon::today();
            $todayDate              = $today->toDateString();
            $normal_master_per      = 38;
            $normal_company_per     = 60;
            $normal_jackpot_per     = 2;

            $sit_master_per         = 40;
            $sit_company_per        = 60;

            $tour_master_per        = 40;
            $tour_company_per       = 60;

            $saleFee                = 0.23;

            //게임머니 <-> 포인트 변활 비율
            $game_info  = GameInfo::where('code', 'holdem')->first();
            $inQuote    = (int)$game_info->inquote;
            $outQuote   = (int)$game_info->outquote;

            //본사 하위 회원 시퀀스 조회
            $user_table = config('admin.database.users_table');
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->whereNotNull($user_table. '.account_id')
                ->where('recommends.step1_id', '=', $this->id)
                ->select('users.account_id')->get();

            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->account_id);
            }

            //일반게임
            $houses = HouseEdge::where('channel', '=', 'normal')
                ->whereDate('log_date', '=', $todayDate)
                ->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $normal_master  = $sum * $normal_master_per / 100;
            $normal_company = $sum * $normal_company_per / 100;
            $normal_jackopt = $sum * $normal_jackpot_per / 100;

            //$outQuote
            $normal_master_point  = floor($normal_master / $outQuote);
            $normal_company_point = floor($normal_company / $outQuote);
            $normal_jackopt_point = floor($normal_jackopt / $outQuote);

            return number_format($normal_jackopt_point);
        });

        $grid->column(trans('admin.realtime_sales.company_total_fee'))->display(function (){
            $today                  = Carbon::today();
            $todayDate              = $today->toDateString();
            $normal_master_per      = 38;
            $normal_company_per     = 60;
            $normal_jackpot_per     = 2;

            $sit_master_per         = 40;
            $sit_company_per        = 60;

            $tour_master_per        = 40;
            $tour_company_per       = 60;

            $saleFee                = 0.23;

            //게임머니 <-> 포인트 변활 비율
            $game_info  = GameInfo::where('code', 'holdem')->first();
            $inQuote    = (int)$game_info->inquote;
            $outQuote   = (int)$game_info->outquote;

            //본사 하위 회원 시퀀스 조회
            $user_table = config('admin.database.users_table');
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->whereNotNull($user_table. '.account_id')
                ->where('recommends.step1_id', '=', $this->id)
                ->select('users.account_id')->get();

            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->account_id);
            }

            //일반게임
            $houses = HouseEdge::where('channel', '=', 'normal')
                ->whereDate('log_date', '=', $todayDate)
                ->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $normal_master  = $sum * $normal_master_per / 100;
            $normal_company = $sum * $normal_company_per / 100;
            $normal_jackopt = $sum * $normal_jackpot_per / 100;

            //$outQuote
            $normal_master_point  = floor($normal_master / $outQuote);
            $normal_company_point = floor($normal_company / $outQuote);
            $normal_jackopt_point = floor($normal_jackopt / $outQuote);

            //싯앤고게임
            $houses = HouseEdge::where('channel', '=',  'sitngo')
                ->whereDate('log_date', '=', $todayDate)
                //->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $sit_master  = $sum * $sit_master_per / 100;
            $sit_company = $sum * $sit_company_per / 100;

            $sit_master_point  = floor($sit_master / $outQuote);
            $sit_company_point = floor($sit_company / $outQuote);

            //토너먼트게임
            $houses = HouseEdge::where('channel', '=', 'tournament')
                ->whereDate('log_date', '=', $todayDate)
                //->whereIn('AccountUniqueID', $userArray)
                ->get();
            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $tour_master  = $sum * $tour_master_per / 100;
            $tour_company = $sum * $tour_company_per / 100;

            $tour_master_point  = floor($tour_master / $outQuote);
            $tour_company_point = floor($tour_company / $outQuote);

            //마스터 수수료 합 (노멀 + 토너먼트 + 샛인고)
            $master_total_fee = $normal_master_point + $tour_master_point + $sit_master_point;

            //본사 수수료 합 (노멀 + 토너먼트 + 싯앤고 )
            $company_total_fee = $normal_company_point + $tour_company_point + $sit_company_point;

            return number_format($company_total_fee);
        });

        $grid->column(trans('admin.realtime_sales.master_total_fee'))->display(function (){
            $today                  = Carbon::today();
            $todayDate              = $today->toDateString();
            $normal_master_per      = 38;
            $normal_company_per     = 60;
            $normal_jackpot_per     = 2;

            $sit_master_per         = 40;
            $sit_company_per        = 60;

            $tour_master_per        = 40;
            $tour_company_per       = 60;

            $saleFee                = 0.23;

            //게임머니 <-> 포인트 변활 비율
            $game_info  = GameInfo::where('code', 'holdem')->first();
            $inQuote    = (int)$game_info->inquote;
            $outQuote   = (int)$game_info->outquote;

            //본사 하위 회원 시퀀스 조회
            $user_table = config('admin.database.users_table');
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->whereNotNull($user_table. '.account_id')
                ->where('recommends.step1_id', '=', $this->id)
                ->select('users.account_id')->get();

            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->account_id);
            }

            //일반게임
            $houses = HouseEdge::where('channel', '=', 'normal')
                ->whereDate('log_date', '=', $todayDate)
                ->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $normal_master  = $sum * $normal_master_per / 100;
            $normal_company = $sum * $normal_company_per / 100;
            $normal_jackopt = $sum * $normal_jackpot_per / 100;

            //$outQuote
            $normal_master_point  = floor($normal_master / $outQuote);
            $normal_company_point = floor($normal_company / $outQuote);
            $normal_jackopt_point = floor($normal_jackopt / $outQuote);

            //싯앤고게임
            $houses = HouseEdge::where('channel', '=',  'sitngo')
                ->whereDate('log_date', '=', $todayDate)
                //->whereIn('AccountUniqueID', $userArray)
                ->get();

            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $sit_master  = $sum * $sit_master_per / 100;
            $sit_company = $sum * $sit_company_per / 100;

            $sit_master_point  = floor($sit_master / $outQuote);
            $sit_company_point = floor($sit_company / $outQuote);

            //토너먼트게임
            $houses = HouseEdge::where('channel', '=', 'tournament')
                ->whereDate('log_date', '=', $todayDate)
                //->whereIn('AccountUniqueID', $userArray)
                ->get();
            $sum = 0;
            foreach ($houses as $house){
                $sum += $house->fee;
            }

            //백분율
            $tour_master  = $sum * $tour_master_per / 100;
            $tour_company = $sum * $tour_company_per / 100;

            $tour_master_point  = floor($tour_master / $outQuote);
            $tour_company_point = floor($tour_company / $outQuote);

            //마스터 수수료 합 (노멀 + 토너먼트 + 샛인고)
            $master_total_fee = $normal_master_point + $tour_master_point + $sit_master_point;

            //본사 수수료 합 (노멀 + 토너먼트 + 싯앤고 )
            $company_total_fee = $normal_company_point + $tour_company_point + $sit_company_point;

            return number_format($master_total_fee);
        });

        $grid->column('not_in_item', trans('admin.realtime_sales.buy_item'))->display(function (){
            //본사 하위 회원 시퀀스 조회
            $user_table = config('admin.database.users_table');
            $users = DB::table('users')
                ->rightJoin('recommends', $user_table.'.id', '=', 'recommends.user_id')
                ->whereNotNull($user_table. '.account_id')
                ->where('recommends.step1_id', '=', $this->id)
                ->select('users.account_id')->get();
            $userArray = array();
            foreach ($users as $user) {
                array_push($userArray, $user->account_id);
            }

            //게임머니 로그 에서 아이템 구매해서 차감된 수치 합산
            $today                  = Carbon::today();
            $todayDate              = $today->toDateString();
            $game_item_money        = 0;
            $logMoneys = LogMoney::whereIn('AccountUniqueID', $userArray)->where('Fluctuation_reason', '9')
                ->whereDate('Fluctuation_date', '=', $todayDate)->get();
            foreach ($logMoneys as $logMoney)
            {
                $replace_val = str_replace('-', '', $logMoney->Fluctuation_money);
                $int_val = floor($replace_val);
                $game_item_money += $int_val;
            }
            return number_format($game_item_money);
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
        /*$show = new Show(DailyinfoCompany::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('search_date', __('Search date'));
        $show->field('total_payment', __('Total payment'));
        $show->field('total_refund', __('Total refund'));
        $show->field('company_chip_payment', __('Company chip payment'));
        $show->field('company_chip_reload', __('Company chip reload'));
        $show->field('company_chip_total', __('Company chip total'));
        $show->field('user_chips', __('User chips'));
        $show->field('user_safe', __('User safe'));
        $show->field('user_deposit', __('User deposit'));
        $show->field('normal_company_fee', __('Normal company fee'));
        $show->field('tour_company_fee', __('Tour company fee'));
        $show->field('sit_company_fee', __('Sit company fee'));
        $show->field('company_rev', __('Company rev'));
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
        /*$form = new Form(new DailyinfoCompany());

        $form->date('search_date', __('Search date'))->default(date('Y-m-d'));
        $form->text('total_payment', __('Total payment'));
        $form->text('total_refund', __('Total refund'));
        $form->text('company_chip_payment', __('Company chip payment'));
        $form->text('company_chip_reload', __('Company chip reload'));
        $form->text('company_chip_total', __('Company chip total'));
        $form->text('user_chips', __('User chips'));
        $form->text('user_safe', __('User safe'));
        $form->text('user_deposit', __('User deposit'));
        $form->text('normal_company_fee', __('Normal company fee'));
        $form->text('tour_company_fee', __('Tour company fee'));
        $form->text('sit_company_fee', __('Sit company fee'));
        $form->text('company_rev', __('Company rev'));

        return $form;*/
    }
}
