<?php

namespace App\Http\Controllers;

use App\DailyinfoBottom;
use App\DailyinfoBottomTotal;
use App\Deposit;
use App\DepositStep;
use App\GameInfo;
use App\GameMember;
use App\GameSafeMoney;
use App\MoneyLog;
use App\Point;
use App\Refund;
use App\RefundStep;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyCalculateSubController extends Controller
{
    public $yesterdayDate;
    public $todayDate;
    public $normal_master_per;
    public $normal_company_per;
    public $normal_jackpot_per;
    public $sit_master_per;
    public $sit_company_per;
    public $tour_master_per;
    public $tour_company_per;
    public $user_table;
    public $company_id;
    public $company_fee;
    public $inQuote;
    public $outQuote;
    public $saleFee;

    public function __construct()
    {
        $yesterday              = Carbon::yesterday();
        //$yesterday              = Carbon::parse('2020-11-29');
        //$yesterday              = Carbon::parse('2020-09-16');
        $this->yesterdayDate    = $yesterday->toDateString();

        $today                  = Carbon::yesterday();
        $this->todayDate        = $today->toDateString();

        //$this->normal_master_per    = 43.75;
        //$this->normal_company_per   = 43.75;
        //$this->normal_company_per   = 100;
        //$this->normal_jackpot_per   = 12.5;

        $this->normal_master_per    = 38;
        $this->normal_company_per   = 60;
        $this->normal_jackpot_per   = 2;

        //$this->sit_master_per       = 50;
        //$this->sit_company_per      = 100;
        $this->sit_master_per       = 40;
        $this->sit_company_per      = 60;

        //$this->tour_master_per      = 50;
        //$this->tour_company_per     = 100;
        $this->tour_master_per      = 40;
        $this->tour_company_per     = 60;

        $this->saleFee  = 0.23;

        $this->user_table = config('admin.database.users_table');

        //게임머니 <-> 포인트 변활 비율
        $game_info  = GameInfo::where('code', 'holdem')->first();
        $this->inQuote    = (int)$game_info->inquote;
        $this->outQuote   = (int)$game_info->outquote;

    }


    public function sub($id)
    {

        Log::channel('calcul_sub')->info('check Date : '. $this->yesterdayDate. ' | category : '. $id);

        $where_query = '';
        $not_query = '';
        if ($id === 'sub_company'){
            $where_query = 'recommends.step2_id';
            $not_query = 'recommends.step3_id';
        }
        elseif ($id === 'distributor'){
            $where_query = 'recommends.step3_id';
            $not_query = 'recommends.step4_id';
        }
        elseif ($id === 'store'){
            $where_query = 'recommends.step4_id';
            $not_query = 'recommends.step5_id';
        }
       // dd($where_query, $not_query);

        //해당 계정 조회
        $subs = DB::table('users')
            ->join('admin_role_users', 'users.id', '=', 'admin_role_users.user_id')
            ->join('admin_roles', 'admin_role_users.role_id', '=', 'admin_roles.id')
            ->where('admin_roles.slug', '=', $id)
            ->select('users.*')
            ->get();

       /* $total_charge_sum = 0;
        $total_refund_sum = 0;
        $total_gamechip_sum = 0;
        $total_safe_sum = 0;
        $total_point_sum = 0;
        $total_rev_sum = 0;*/

        foreach ($subs as $sub) {
            //부본 계정의 시퀀스 아이디 조회
            $sub_id     = $sub->id;
            $sub_name   = $sub->username;
            $fee        = $sub->profit;

            //해당 하부 회원들 조회
            $lows = DB::table('users')
                ->join('recommends', 'users.id', '=', 'recommends.user_id')
                ->where($where_query, '=', $sub_id)
                ->whereNotNull($not_query)
                ->select('users.*')->get();

            $userArray = array();
            foreach ($lows as $low) {
                array_push($userArray, $low->id);
            }
            $gameArray = array();
            foreach ($lows as $low) {
                array_push($gameArray, $low->account_id);
            }

            //유저 충전 총액
            $step = DepositStep::where('code', 'success')->first();
            $user_charge_total = Deposit::whereDate('created_at', '=', $this->yesterdayDate)
                ->where('step_id', $step->id)
                ->whereIn('user_id', $userArray)
                ->sum('charge_amount');
            echo '유저 충전 수량 총합 : ' . $user_charge_total;
            echo '<br><br>';

            //유저 환전 총액
            $refund_step = RefundStep::where('code', 'refund_ok')->first();
            $user_refund_total = Refund::whereDate('created_at', '=', $this->yesterdayDate)
                ->whereIn('user_id', $userArray)
                ->where('step_id', $refund_step->id)
                ->sum('amount');
            echo '유저 환전 총액 : ' . $user_refund_total;
            echo '<br><br>';

            //유져 게인 머니 총액
            $game_chips = GameMember::whereIn('AccountUniqueid', $gameArray)
                ->select(DB::raw('sum(convert(bigint,(convert(decimal(38), Have_Money) / 100000000))) as chips'))
                ->get();
            $game_total_chips = 0;
            foreach ($game_chips as $game_chip){
                $game_total_chips = $game_chip->chips / 100 ?? 0;
            }
            echo '유저 게임머니 총합 : ' . $game_total_chips;
            echo '<br><br>';

            //유저 금고 총합
            $safe_amount = GameSafeMoney::whereIn('user_id', $userArray)->sum('safe_money');
            echo '유저 금고 보유 현황 : ' .$safe_amount;
            echo '<br><br>';

            //유저 디파짓 총합
            $add_cnt = Point::whereIn('user_id', $userArray)->where('use_point', '=', '0')->sum('point');
            $minus_cnt = Point::whereIn('user_id', $userArray)->where('point', '=', '0')->sum('use_point');
            $user_point   = $add_cnt - $minus_cnt;
            echo '유저 디파짓 누적 현황 : ' . $user_point;
            echo '<br><br>';

            //수익
            //(판매금액 x 0.295) x 계정비율% =  수익
            /*
             * 판매금액 : $user_charge_total
             *$this->saleFee
             *본사 비율 : $this->본사비율
             * */
            //수수료를 백분율로 환산
            $fee_back = $fee / 100;
            $rev = ($user_charge_total * $this->saleFee) * $fee_back;
            //dd($user_charge_total);
            echo '수익 : ' .$rev;

            echo '<br><br>';
            echo '-----------------';
            echo '<br><br>';

            DailyinfoBottom::create([
                'search_date' => $this->yesterdayDate,
                'level' => $id,
                'user_id' => $sub_id,
                'username' => $sub_name,
                'total_payment' => $user_charge_total,
                'total_refund' => $user_refund_total,
                'user_chips' => $game_total_chips,
                'user_safe' => $safe_amount,
                'user_deposit' => $user_point,
                'rev' => $rev,
            ]);


            //각 계정 수익을 머니로그 테이블에 등록
            //등록전 이전 레코드셋 에 now_amount 조회
            $logCnt = MoneyLog::where('user_id', $sub_id)->count();

            if ($logCnt === 0){
                $rev_amount = 0;
            } else {
                $moneylog = MoneyLog::where('user_id', $sub_id)->orderby('id', 'desc')->first();
                $rev_amount = $moneylog->now_amount;
            }

            if ((int)$rev > 0) {
                $chg_amount = $rev;
                $now_amount = $rev_amount + $chg_amount;

                MoneyLog::create([
                    'search_date' => $this->yesterdayDate,
                    'level' => $id,
                    'user_id' => $sub_id,
                    'username' => $sub_name,
                    'rev_amount' => $rev_amount,
                    'chg_amount' => $chg_amount,
                    'now_amount' => $now_amount,
                    'reason' => 'profit',
                ]);
            }


            /*$total_charge_sum       += $user_charge_total;
            $total_refund_sum       += $user_refund_total;
            $total_gamechip_sum     += $game_total_chips;
            $total_safe_sum         += $safe_amount;
            $total_point_sum        += $user_point;
            $total_rev_sum          += $rev;*/

            //수익 을 총합 테이블에 등록해 준다.
            $cnt = DailyinfoBottomTotal::where('user_id', $sub_id)->count();
            if($cnt > 0){
                $total = DailyinfoBottomTotal::where('user_id', $sub_id)->first();
                $total->rev += $rev;
                $total->save();
            } else {
                DailyinfoBottomTotal::create([
                    'level' => $id,
                    'user_id' => $sub_id,
                    'username' => $sub_name,
                    'rev' => $rev,
                ]);
            }
        }
    }
    public function userlist()
    {
        //제일 하단 user 권한 계정만 조회
        $subs = DB::table('users')
            ->join('admin_role_users', 'users.id', '=', 'admin_role_users.user_id')
            ->join('admin_roles', 'admin_role_users.role_id', '=', 'admin_roles.id')
            ->where('admin_roles.slug', '=', 'user')
            ->select('users.*')
            ->get();

        foreach ($subs as $sub)
        {
            $sub_id     = $sub->id;
            $sub_name   = $sub->username;
            $sub_game   = $sub->account_id;
            $sub_create = $sub->created_at;

            echo '유저 아이디 : ' . $sub_name;
            echo ' | ';

            //유저 닉네임
            $user_nick = GameMember::where('AccountUniqueid', $sub_game)->value('PlayerName');
            echo '유저 닉네임 : ' . $user_nick;
            echo ' | ';

            //유저 충전 총액
            $step = DepositStep::where('code', 'success')->first();
            $user_charge_total = Deposit::whereDate('created_at', '=', $this->yesterdayDate)
                ->where('step_id', $step->id)
                ->where('user_id', $sub_id)
                ->sum('charge_amount');
            echo '유저 충전 수량 총합 : ' . $user_charge_total;
            echo ' | ';

            //유저 환전 총액
            $refund_step = RefundStep::where('code', 'refund_ok')->first();
            $user_refund_total = Refund::whereDate('created_at', '=', $this->yesterdayDate)
                ->where('user_id', $sub_id)
                ->where('step_id', $refund_step->id)
                ->sum('amount');
            echo '유저 환전 총액 : ' . $user_refund_total;
            echo ' | ';

            //유져 게인 머니 총액
            $game_chips = GameMember::where('AccountUniqueid', $sub_game)
                ->select(DB::raw('sum(convert(bigint,(convert(decimal(38), Have_Money) / 100000000))) as chips'))
                ->get();
            $game_total_chips = 0;
            foreach ($game_chips as $game_chip){
                $game_total_chips = $game_chip->chips ?? 0;
            }
            echo '유저 게임머니 총합 : ' . $game_total_chips;
            echo ' | ';

            echo '유저 가입일 : ' . $sub_create;

            echo '<br><br>';

        }

    }
}
