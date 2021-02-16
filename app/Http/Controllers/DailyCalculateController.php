<?php

namespace App\Http\Controllers;


use App\Commission;
use App\DailyInfoLosing;
use App\DailyInfoLosingTotal;
use App\DailyInfoRolling;
use App\Deposit;
use App\DepositStep;
use App\Headquarter;
use App\HeadquarterDeposit;
use App\LosingPoint;
use App\Point;
use App\Recommend;
use App\Refund;
use App\RefundStep;
use App\SlotGameAuth;
use App\SlotGameMoneyIn;
use App\SlotGameMoneyOut;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DailyCalculateController extends Controller
{
    public $yesterdateBeforeDate;
    public $yesterdayDate;
    public $todayDate;
    public $inQuote;
    public $outQuote;
    public $saleFee;
    public $user_table;

    public function __construct()
    {
        $yesterday              = Carbon::yesterday();
        //$yesterday              = Carbon::parse('2021-01-17');
        $this->yesterdayDate    = $yesterday->toDateString();

        $today                  = Carbon::today();
        $this->todayDate        = $today->toDateString();

        $yesterdayBefore = Carbon::yesterday()->addDay(-1);
        $this->yesterdateBeforeDate = $yesterdayBefore->toDateString();

        $this->user_table = config('admin.database.users_table');
    }

    public function index()
    {
        Log::channel('calcul_losing_user')->info('check Date : '. $this->yesterdayDate);

        //본사 계정 조회
        $companys = DB::table('users')
            ->join('admin_role_users', 'users.id', '=', 'admin_role_users.user_id')
            ->join('admin_roles', 'admin_role_users.role_id', '=', 'admin_roles.id')
            ->where('admin_roles.slug', '=', 'company')
            ->select('users.*')
            ->get();


        foreach ($companys as $company)
        {
            echo '본사계정 : ' . $company->username;
            echo '----->';


            /*$heads = Headquarter::where('full_ok', '0')->where('user_id', $company->id)->get();
            $sum_amount = 0;
            foreach ($heads as $head){
                $deposits = HeadquarterDeposit::where('head_id', '=', $head->id)->sum('deposit_point');
                $sum_amount += (int)$deposits;
            }
            if ($sum_amount > 0){
                $minus_amount = -$sum_amount;
                $minus_amount = $minus_amount * 10;
            } else {
                $minus_amount = 0;
            }
            echo '입금대기 : ' . $minus_amount . ' | ';



            //어제날짜 입금 총합
            $headDeposits = HeadquarterDeposit::whereDate('created_at', '=', $this->yesterdayDate)
                ->select(DB::raw('sum(deposit_point) as total_amount'), DB::raw('head_id'))
                ->groupBy('head_id')->get();

            $deposit_amount = 0;
            foreach ($headDeposits as $headDeposit) {
                //건의 수량
                $deposit_amount += $headDeposit->total_amount;
                //echo '입금건 아이디 : ' . $headDeposit->head_id. ' | ';
                //echo '해당날짜에 입금한 금액은 ' . $headDeposit->total_amount. ' | ';
                //echo '<br>';
            }
            echo '입금 금액 : ' . $deposit_amount;
            echo ' | ';

            //정산금액
            $sum_deposits = $minus_amount + $deposit_amount;
            echo '정산 : ' . $sum_deposits;

            echo '<br>';*/




            //본사하위 일반 계정 부터 조회
            $users = DB::table($this->user_table)
                ->rightJoin('recommends', $this->user_table.'.id', '=', 'recommends.user_id')
                //->whereNotNull($this->user_table. '.account_id')
                ->where('recommends.step1_id', '=', $company->id)
                ->whereNotNull('recommends.step2_id')
                ->whereNotNull('recommends.step3_id')
                ->whereNotNull('recommends.step4_id')
                ->whereNotNull('recommends.step5_id')
                ->select('users.*')->get();

            echo '하위 일반회원 카운트 : ' . $users->count();
            echo '<br>';

            //일반 회원 루프
            foreach ($users as $user)
            {
                echo $user->username;
                echo ' | ';


                //유저 충전 총액
                $step               = DepositStep::where('code', 'success')->first();
                $user_charge_total  = Deposit::whereDate('updated_at', '=', $this->yesterdayDate)
                    ->where('step_id', $step->id)
                    ->where('user_id', $user->id)
                    ->sum('charge_amount');
                echo '유저 충전금 : ' . $user_charge_total;
                echo ' | ';


                //유저 환전 총액
                $refund_step        = RefundStep::where('code', 'refund_ok')->first();
                $user_refund_total  = Refund::whereDate('updated_at', '=', $this->yesterdayDate)
                    ->where('step_id', $refund_step->id)
                    ->where('user_id', $user->id)
                    ->sum('amount');
                echo '유저 출금 : ' . $user_refund_total;
                echo ' | ';


                //현재 유저 포인트 총합
                $add_cnt        = Point::where('user_id', $user->id)->where('use_point', '=', '0')->sum('point');
                $minus_cnt      = Point::where('user_id', $user->id)->where('point', '=', '0')->sum('use_point');
                $user_point     = $add_cnt - $minus_cnt;

                //게임 머니로 변환 신청 했으나 아직 금고 에 있는 포인트 조회 후 합산
                $slotMoneyIn_amount = SlotGameMoneyIn::where('Aid', $user->account_id)->where('flag', '0')->sum('Val1');

                $user_point += $slotMoneyIn_amount;
                //$user_point += $slotMOneyOut_amount;

                echo '유저 현재 보유 포인트 : ' . $user_point;
                echo ' | ';


                $game_total_chips = 0;
                //해당 회원 게임 머니 조회
                $game_auth = SlotGameAuth::where('Aid', $user->account_id)->first();
                if ($game_auth) {
                    $game_total_chips += $game_auth->Chip;
                }

                //게임머니에서 포인트로 변화할때 신청했지만, 금고에 존재하는 것 게임머니로  조회 및 합산
                $slotMOneyOut_amount    = SlotGameMoneyOut::where('Aid', $user->account_id)->where('flag', '0')->sum('SaveMoney');
                $game_total_chips       += $slotMOneyOut_amount;

                echo '유저 현재 게임 머니 : ' . $user_point;
                echo ' | ';


                //유저 정산 기간 포인트 총합
                $user_term_point = Point::where('user_id', $user->id)
                    ->where('use_point', '=', '0')
                    ->whereDate('created_at', '=', $this->yesterdayDate)
                    ->sum('point');
                $user_term_point += $slotMoneyIn_amount;
                //$user_term_point += $slotMOneyOut_amount;
                echo '유저 기간 충전 포인트 : ' . $user_term_point;
                echo ' | ';

                //유저의 이전 정산시 찍힌 보유 포인트, 유저게임머니 를 가져온다
                $past_losing_data   = DailyInfoLosing::where('user_id', $user->id)->where('search_date', $this->yesterdateBeforeDate)->first();
                $past_user_point    = ($past_losing_data === null) ? 0 : $past_losing_data->total_point;
                $past_game_chips    = ($past_losing_data === null) ? 0 : $past_losing_data->total_game_money;


                //해당 유저 루징금액 계산
                // 루징금액 = (기간동안 입금신청 된 금액  + 이전 정산시 찍힌 보유 포인트 + 이전 정산시 찍힌 유저게임머니 ) - (기간 출금 승인된 포인트 + 현재 보유 포인트 + 유저보유게임머니))
                //$user_losing_amount = ($user_term_point + $past_user_point) - $user_refund_total - $user_point;
                //$user_losing_amount = ($user_charge_total + $past_user_point) - ($user_refund_total + $user_point + $game_total_chips);
                $user_losing_amount = $user_charge_total + $past_user_point + $past_game_chips;
                $user_losing_amount -= $user_refund_total;
                $user_losing_amount -= $user_point;
                $user_losing_amount -= $game_total_chips;

                echo '유저 루징 금액 : ' . $user_losing_amount;
                echo ' | ';
               /* if ($user->username === 'cocovip18'){
                    dd($user_charge_total, $past_user_point, $user_refund_total, $user_point, $user_losing_amount);
                }*/

                //이제 루징금액을 각 상부에 퍼센트율로 나눠 준다
                $user_recommend = Recommend::where('user_id', $user->id)->first();



                //매장 가져갈 루징 조회
                $store_commission        = Commission::where('user_id', $user_recommend->step4_id)->first();
                $store_losing_per        = (int)$store_commission->losing;
                $store_losing            =  $user_losing_amount * $store_losing_per / 100;
                $store_losing_revenue    = ($store_losing > 0) ? $store_losing : 0;
                echo '매장 계정 루징 : ' .  $store_losing;
                echo '(매장 계정 루징 수익 : ' . $store_losing_revenue . ') ';
                echo ' | ';


                //총판  가져갈 루징 조회
                //총판 부터는 내려준거 를 차감 시키고 계산해준다.
                $dist_commission         = Commission::where('user_id', $user_recommend->step3_id)->first();
                $dist_losing_per        = (int)$dist_commission->losing;
                $dist_losing_final      = $dist_losing_per - $store_losing_per;

                $dist_losing            =  $user_losing_amount * $dist_losing_final / 100;
                $dist_losing_revenue    =  ($dist_losing > 0) ? $dist_losing : 0;
                echo '총판 계정 루징 : ' .  $dist_losing;
                echo '(총판 계정 루징 수익 : ' . $dist_losing_revenue . ') ';
                echo ' | ';


                //부본 가져갈 루징 조회
                //부본은 내려준거 를 차감 시키고 계산해준다.
                $sub_commission         = Commission::where('user_id', $user_recommend->step2_id)->first();
                $sub_losing_per        = (int)$sub_commission->losing;
                $sub_losing_final      = $sub_losing_per - $dist_losing_per;

                $sub_losing            =  $user_losing_amount * $sub_losing_final / 100;
                $sub_losing_revenue    =  ($sub_losing > 0) ? $sub_losing : 0;
                echo '부본 계정 루징 : ' .  $sub_losing;
                echo '(부본 계정 루징 수익 : ' . $sub_losing_revenue . ') ';
                echo ' | ';


                //본사 가져갈 루징 조회
                //본사는 내려준거 를 차감 시키고 계산해준다.
                $com_commission         = Commission::where('user_id', $user_recommend->step1_id)->first();
                $com_losing_per        = (int)$com_commission->losing;
                $com_losing_final      = $com_losing_per - $sub_losing_per;

                $com_losing            =  $user_losing_amount * $com_losing_final / 100;
                $com_losing_revenue    =   ($com_losing > 0) ? $com_losing : 0;
                echo '본사 계정 루징 : ' .  $com_losing;
                echo '(본사 계정 루징 수익 : ' . $com_losing_revenue . ') ';
                echo ' | ';


                //데이터 등록 시작
                $dailylog = new DailyInfoLosing;
                $dailylog->search_date = $this->yesterdayDate;
                $dailylog->user_id = $user->id;
                $dailylog->username = $user->username;
                $dailylog->total_deposit = $user_charge_total;
                $dailylog->total_refund = $user_refund_total;
                $dailylog->total_point = $user_point;
                $dailylog->term_point = $user_term_point;
                $dailylog->past_user_point = $past_user_point;
                $dailylog->total_game_money = $game_total_chips;
                $dailylog->user_losing = $user_losing_amount;
                $dailylog->store_id = $user_recommend->step4_id;
                $dailylog->store_commission = $store_losing_per;
                $dailylog->store_losing_revenue = $store_losing_revenue;
                $dailylog->store_losing = $store_losing;
                $dailylog->dist_id = $user_recommend->step3_id;
                $dailylog->dist_commission = $dist_losing_per;
                $dailylog->dist_commission_final = $dist_losing_final;
                $dailylog->dist_losing = $dist_losing;
                $dailylog->dist_losing_revenue = $dist_losing_revenue;
                $dailylog->sub_id = $user_recommend->step2_id;
                $dailylog->sub_commission = $sub_losing_per;
                $dailylog->sub_commission_final = $sub_losing_final;
                $dailylog->sub_losing = $sub_losing;
                $dailylog->sub_losing_revenue = $sub_losing_revenue;
                $dailylog->com_id = $user_recommend->step1_id;
                $dailylog->com_commission = $com_losing_per;
                $dailylog->com_commission_final = $com_losing_final;
                $dailylog->com_losing = $com_losing;
                $dailylog->com_losing_revenue = $com_losing_revenue;
                $dailylog->save();


                //각 하부 루징 포인트 로그에 등록
                /*
                 *
                $store_point = new LosingPoint;
                $store_point->user_id = $user_recommend->step4_id;
                $store_point->losing_id = $dailylog->id;
                $store_point->po_content = 'daily_cal';
                $store_point->point = $dailylog->store_losing_revenue;
                $store_point->use_point = '0';
                $store_point->mb_point = '0';
                $store_point->cal_date = $this->yesterdayDate;
                $store_point->save();

                //루징 수익 회원 테이블에 더해준다.
                $store_user = User::find($user_recommend->step4_id);
                $store_user->losing_cnt += $dailylog->store_losing_revenue;
                $store_user->save();

                $dist_point = new LosingPoint;
                $dist_point->user_id = $user_recommend->step3_id;
                $dist_point->losing_id = $dailylog->id;
                $dist_point->po_content = 'daily_cal';
                $dist_point->point = $dailylog->dist_losing_revenue;
                $dist_point->use_point = '0';
                $dist_point->mb_point = '0';
                $dist_point->cal_date = $this->yesterdayDate;
                $dist_point->save();

                //루징 수익 회원 테이블에 더해준다.
                $dist_user = User::find($user_recommend->step3_id);
                $dist_user->losing_cnt += $dailylog->dist_losing_revenue;
                $dist_user->save();

                $sub_point = new LosingPoint;
                $sub_point->user_id = $user_recommend->step2_id;
                $sub_point->losing_id = $dailylog->id;
                $sub_point->po_content = 'daily_cal';
                $sub_point->point = $dailylog->sub_losing_revenue;
                $sub_point->use_point = '0';
                $sub_point->mb_point = '0';
                $sub_point->cal_date = $this->yesterdayDate;
                $sub_point->save();

                //루징 수익 회원 테이블에 더해준다.
                $sub_user = User::find($user_recommend->step2_id);
                $sub_user->losing_cnt += $dailylog->sub_losing_revenue;
                $sub_user->save();

                $com_point = new LosingPoint;
                $com_point->user_id = $user_recommend->step1_id;
                $com_point->losing_id = $dailylog->id;
                $com_point->po_content = 'daily_cal';
                $com_point->point = $dailylog->com_losing_revenue;
                $com_point->use_point = '0';
                $com_point->mb_point = '0';
                $com_point->cal_date = $this->yesterdayDate;
                $com_point->save();

                //루징 수익 회원 테이블에 더해준다.
                $com_user = User::find($user_recommend->step1_id);
                $com_user->losing_cnt += $dailylog->com_losing_revenue;
                $com_user->save();
                */


                echo '<br>';
            }

            echo '<br>';
        }
    }


    public function rolling()
    {
        //현재 정산을 구분할수 있는 유니크 값을 만든다.
        $this_roll_unique = Str::random(30);

        //본사 계정 조회
        $companys = DB::table('users')
            ->join('admin_role_users', 'users.id', '=', 'admin_role_users.user_id')
            ->join('admin_roles', 'admin_role_users.role_id', '=', 'admin_roles.id')
            ->where('admin_roles.slug', '=', 'company')
            ->select('users.*')
            ->get();


        foreach ($companys as $company)
        {
            echo '본사계정 : ' . $company->username;
            echo '----->';

            //루프 돌리기 전에 하부 계정들의 롤링 값을 초기화 해준다.
            $subs = DB::table($this->user_table)
                ->rightJoin('recommends', $this->user_table.'.id', '=', 'recommends.user_id')
                //->whereNotNull($this->user_table. '.account_id')
                ->where('recommends.step1_id', '=', $company->id)
                ->whereNull('recommends.step5_id')
                ->select('users.*')->get();
            $subArray = array();
            foreach ($subs as $sub) {
                array_push($subArray, $sub->id);
            }
            $sub_del = User::whereIn('id', $subArray)->update(['admin_rolling' => '0']);


            //본사하위 일반 계정 부터 조회
            $users = DB::table($this->user_table)
                ->rightJoin('recommends', $this->user_table.'.id', '=', 'recommends.user_id')
                //->whereNotNull($this->user_table. '.account_id')
                ->where('recommends.step1_id', '=', $company->id)
                ->whereNotNull('recommends.step2_id')
                ->whereNotNull('recommends.step3_id')
                ->whereNotNull('recommends.step4_id')
                ->whereNotNull('recommends.step5_id')
                ->select('users.*')->get();

            echo '하위 일반회원 카운트 : ' . $users->count();
            echo '<br>';

            //일반 회원 루프
            foreach ($users as $user)
            {
                echo $user->username;
                echo ' | ';

                //$user_rolling = $user->slot_betting_amount;
                $user_rolling = 0;

                $game_auth = SlotGameAuth::where('Aid', $user->id)->first();
                if ($game_auth){
                    $user_rolling = $game_auth->BettingAccumulate;
                }

                echo '회원 베팅액 : ' .$user_rolling ;
                echo ' | ';

                //이제 롤링금액을 각 상부에 퍼센트율로 나눠 준다
                $user_recommend = Recommend::where('user_id', $user->id)->first();

                //매장 가져갈 롤링 조회
                $store_commission        = Commission::where('user_id', $user_recommend->step4_id)->first();
                $store_rolling_per        = $store_commission->rolling;
                $store_rolling            =  $user_rolling * $store_rolling_per / 100;
                echo '매장 계정 롤링 ('. $store_rolling_per .'%) : ' .  $store_rolling;
                echo ' | ';


                //총판  가져갈 롤링 조회
                //총판 부터는 내려준거 를 차감 시키고 계산해준다.
                $dist_commission         = Commission::where('user_id', $user_recommend->step3_id)->first();
                $dist_rolling_per        = $dist_commission->rolling;
                $dist_rolling_final      = $dist_rolling_per - $store_rolling_per;

                $dist_rolling            =  $user_rolling * $dist_rolling_final / 100;
                echo '총판 계정 롤링 ('. $dist_rolling_final .'%) : ' .  $dist_rolling;
                echo ' | ';


                //부본 가져갈 롤링 조회
                //부본은 내려준거 를 차감 시키고 계산해준다.
                $sub_commission         = Commission::where('user_id', $user_recommend->step2_id)->first();
                $sub_rolling_per        = $sub_commission->rolling;
                $sub_rolling_final       = $sub_rolling_per - $dist_rolling_per;

                $sub_rolling            =  $user_rolling * $sub_rolling_final / 100;
                echo '부본 계정 롤링 ('. $sub_rolling_final .'%) : ' .  $sub_rolling;
                echo ' | ';

                //본사 가져갈 롤링 조회
                //본사는 내려준거 를 차감 시키고 계산해준다.
                $com_commission         = Commission::where('user_id', $user_recommend->step1_id)->first();
                $com_rolling_per        = (int)$com_commission->rolling;
                $com_rolling_final      = $com_rolling_per - $sub_rolling_per;

                $com_rolling            =  $user_rolling * $com_rolling_final / 100;
                echo '본사 계정 롤링 ('. $com_rolling_final .'%) : ' .  $com_rolling;


                //데이터 등록 시작
                $dailylog = new DailyInfoRolling;
                $dailylog->rolling_cd = $this_roll_unique;
                $dailylog->user_id = $user->id;
                $dailylog->username = $user->username;
                $dailylog->user_rolling =$user_rolling;
                $dailylog->store_id = $user_recommend->step4_id;
                $dailylog->store_commission = $store_rolling_per;
                $dailylog->store_rolling = $store_rolling;
                $dailylog->dist_id = $user_recommend->step3_id;
                $dailylog->dist_commission = $dist_rolling_per;
                $dailylog->dist_commission_final = $dist_rolling_final;
                $dailylog->dist_rolling = $dist_rolling;
                $dailylog->sub_id = $user_recommend->step2_id;
                $dailylog->sub_commission = $sub_rolling_per;
                $dailylog->sub_commission_final = $sub_rolling_final;
                $dailylog->sub_rolling = $sub_rolling;
                $dailylog->com_id = $user_recommend->step1_id;
                $dailylog->com_commission = $com_rolling_per;
                $dailylog->com_commission_final = $com_rolling_final;
                $dailylog->com_rolling = $com_rolling;
                $dailylog->save();


                //매장 롤링 업데이트
                $store_user = User::find($user_recommend->step4_id);
                $store_user->admin_rolling += $store_rolling;
                $store_user->save();

                //총판 롤링 업데이트
                $dist_user = User::find($user_recommend->step3_id);
                $dist_user->admin_rolling += $dist_rolling;
                $dist_user->save();

                //부본 롤링 업데이트
                $sub_user = User::find($user_recommend->step2_id);
                $sub_user->admin_rolling += $sub_rolling;
                $sub_user->save();

                //본사 롤링 업데이트
                $com_user = User::find($user_recommend->step1_id);
                $com_user->admin_rolling += $com_rolling;
                $com_user->save();



                echo '<br>';
            }

            echo '<br>';
        }
    }
}
