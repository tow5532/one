<?php

namespace App\Http\Controllers;

use App\Commission;
use App\DailyInfoUserNo;
use App\DailyInfoUserRolling;
use App\DailyInfoUserRollingResult;
use App\DailyInfoUserRollingTotal;
use App\HeadquarterLog;
use App\Recommend;
use App\SlotGameAuth;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DailyInfoMinRollingController extends Controller
{
    public $user_table;
    public $todayDate;

    public function __construct()
    {
        $this->user_table = config('admin.database.users_table');

        $today                   = Carbon::today();
        $this->todayDate        = $today->toDateString();
    }

    public function user()
    {
        //현재 정산을 구분할수 있는 유니크 값을 만든다.
        $this_roll_unique = Str::random(50);

        //본사 계정 조회
        $companys = DB::table('users')
            ->join('admin_role_users', 'users.id', '=', 'admin_role_users.user_id')
            ->join('admin_roles', 'admin_role_users.role_id', '=', 'admin_roles.id')
            ->where('admin_roles.slug', '=', 'company')
            ->select('users.*')
            ->get();

        $data_array = [];

        //본사 루프 시작
        foreach ($companys as $i => $company)
        {
            echo '본사계정 : ' . $company->username;
            echo '----->';

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

            $data_array[$i] = [
                'company_id' => $company->id,
                'company_username' => $company->username,
                'user_cnt' => $users->count()
            ];

            //일반 회원 루프
            foreach ($users as $key => $user) {
                echo $user->username;
                echo ' | ';

                $user_rolling = 0;

                if ($user->account_id) {
                    $game_auth = SlotGameAuth::where('Aid', $user->account_id)->first();
                    if ($game_auth) {
                        $user_rolling = (int)$game_auth->BettingAccumulate;
                    }
                }

                echo '회원 베팅액 : ' .$user_rolling ;
                echo ' | ';

                $data_array[$i]['users'][$key] = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'user_betting' => $user_rolling
                ];



                echo '<br>';
            }


            echo '<br>';
        }

        //데이터 등록 시작
        $dailylog = new DailyInfoUserRolling();
        $dailylog->search_date = $this->todayDate;
        $dailylog->rolling_cd = $this_roll_unique;
        $dailylog->user_arr = $data_array;
        $dailylog->save();

        //최종 유저 롤링 시퀀스 등록
        $rollingNo = new DailyInfoUserNo;
        $rollingNo->running_idx = $dailylog->id;
        $rollingNo->save();

        //하부계정 시작#######################################################


        //최종 롤링 유저 시퀀스 조회
        //$rollingNo = DailyInfoUserNo::orderBy('running_idx', 'desc')->first();

        //해당 json 데이터 조회
        $rolling = DailyInfoUserRolling::find($rollingNo->running_idx);
        $companys = $rolling->user_arr;

        foreach ($companys as $company)
        {
            echo '본사계정 : '. $company['company_username'];
            echo '----->';
            echo '<br>';

            echo '하위 일반회원 카운트 : ' . $company['user_cnt'];
            echo '<br>';

            if ($company['user_cnt'] > 0){

                //해당 본사 계정을 가지고 있는 매장계정부터조회
                //매장 부터 거꾸로 위로 올라간다. 수수료 계산 때문에~
                $stores = Recommend::where('step1_id', $company['company_id'])
                    ->whereNotNull('step2_id')
                    ->whereNotNull('step3_id')
                    ->whereNotNull('step4_id')
                    ->whereNull('step5_id')
                    ->get();

                foreach ($stores as $store)
                {
                    //매장부터 롤링 정산 시작
                    //해당 매장 하부 일반회원 계정조회
                    $users = Recommend::where('step4_id', $store->user_id)->whereNotNull('step5_id')->select('user_id')->get();

                    //해당 매장 회원, 등급 정보
                    $user_info      = User::find($store->user_id);
                    $user_grade     = DB::table('admin_role_users')->where('user_id', $store->user_id)->first();
                    $user_role      = DB::table('admin_roles')->where('id', $user_grade->role_id)->first();

                    //본사의 하위 일반 유저 루프를 돌면서, 해당 매장회원의 일반 회원인지 확인하여
                    //존재 한다면, 베팅금액 을 더해준다.
                    $comusers = $company['users'];
                    $total_betting_cnt = 0;

                    foreach ($comusers as $comuser)
                    {
                        foreach ($users as $user)
                        {
                            if ($user->user_id === $comuser['user_id']){
                                $total_betting_cnt += $comuser['user_betting'];
                            }
                        }
                    }

                    //롤링 수익 계산 시작
                    $store_commission           = Commission::where('user_id', $store->user_id)->first();
                    $store_rolling_per          = $store_commission->rolling;
                    $store_rolling              =  $total_betting_cnt * $store_rolling_per / 100;

                    //매장 계정 롤링 정보 등록 시작
                    $total = new DailyInfoUserRollingTotal;
                    $total->rolling_id = $rollingNo->running_idx;
                    $total->user_id = $store->user_id;
                    $total->username = $user_info->username;
                    $total->user_role = $user_role->slug;
                    $total->total_betting = $total_betting_cnt;
                    $total->commission = $store_rolling_per;
                    $total->commission_final = $store_rolling_per;
                    $total->rolling = $store_rolling;
                    $total->save();

                    //롤링 수량 업데이트
                    $user_info->admin_rolling = $store_rolling;
                    $user_info->save();


                    //상위 계정 조회 해서 거꾸로 거슬러 올라간다.
                    $store_recommend = Recommend::where('user_id', $store->user_id)->first();


                    //######총판#######
                    $dist_user_id   = $store_recommend->step3_id;

                    //해당 매장 회원, 등급 정보
                    $user_info      = User::find($dist_user_id);
                    $user_grade     = DB::table('admin_role_users')->where('user_id', $user_info->id)->first();
                    $user_role      = DB::table('admin_roles')->where('id', $user_grade->role_id)->first();

                    //롤링 수익 계산 시작
                    $dist_commission           = Commission::where('user_id', $dist_user_id)->first();
                    $dist_rolling_per          = $dist_commission->rolling;
                    $dist_rolling_final         = $dist_rolling_per - $store_rolling_per;
                    $dist_rolling              =  $total_betting_cnt * $dist_rolling_final / 100;

                    $total = new DailyInfoUserRollingTotal;
                    $total->rolling_id = $rollingNo->running_idx;
                    $total->user_id = $dist_user_id;
                    $total->username = $user_info->username;
                    $total->user_role = $user_role->slug;
                    $total->total_betting = $total_betting_cnt;
                    $total->commission = $dist_rolling_per;
                    $total->commission_final = $dist_rolling_final;
                    $total->rolling = $dist_rolling;
                    $total->save();


                    //#######부본##############
                    $sub_user_id    = $store_recommend->step2_id;

                    //해당 매장 회원, 등급 정보
                    $user_info      = User::find($sub_user_id);
                    $user_grade     = DB::table('admin_role_users')->where('user_id', $user_info->id)->first();
                    $user_role      = DB::table('admin_roles')->where('id', $user_grade->role_id)->first();

                    //롤링 수익 계산 시작
                    $sub_commission            = Commission::where('user_id', $sub_user_id)->first();
                    $sub_rolling_per           = $sub_commission->rolling;
                    $sub_rolling_final         = $sub_rolling_per - $dist_rolling_per;
                    $sub_rolling              =  $total_betting_cnt * $sub_rolling_final / 100;

                    //부본 계정 롤링 정보 등록 시작
                    $total = new DailyInfoUserRollingTotal;
                    $total->rolling_id = $rollingNo->running_idx;
                    $total->user_id = $sub_user_id;
                    $total->username = $user_info->username;
                    $total->user_role = $user_role->slug;
                    $total->total_betting = $total_betting_cnt;
                    $total->commission = $sub_rolling_per;
                    $total->commission_final = $sub_rolling_final;
                    $total->rolling = $sub_rolling;
                    $total->save();
                }


                echo '<br>';
            }

        }

        //total 테이블에 해당 roll_idx 를 그룹바이로 검색 하다.
        $lists = DailyInfoUserRollingTotal::select('user_id', DB::raw("sum(rolling) as rolling_cnt"), DB::raw('sum(total_betting) as total_betting_cnt'))
            ->where('rolling_id', $rollingNo->running_idx)
            ->groupBy('user_id')
            ->get();

        foreach ($lists as $list)
        {
            //해당 매장 회원, 등급 정보
            $user_info      = User::find($list->user_id);
            $user_grade     = DB::table('admin_role_users')->where('user_id', $user_info->id)->first();
            $user_role      = DB::table('admin_roles')->where('id', $user_grade->role_id)->first();

            // 기존에 회원이 출금한 내역이 있다면, 출금 누적 금액을 해당회원 롤링 수익에서 차감 해준다.
            $refund_amount =  HeadquarterLog::where('user_id', $list->user_id)
                ->where('use_point', '=', '0')
                ->where('po_content', 'change_rolling')
                ->sum('point');
            $rolling_result = $list->rolling_cnt - $refund_amount;

            $result = new DailyInfoUserRollingResult;
            $result->rolling_id = $rollingNo->running_idx;
            $result->user_id = $list->user_id;
            $result->username = $user_info->username;
            $result->user_role = $user_role->slug;
            $result->total_betting = $list->total_betting_cnt;
            $result->refund_amount = $refund_amount;
            $result->rolling = $rolling_result;
            $result->save();

            //해당 유저 회원 테이블에 롤링 포인트 업데이트
            $user_info->admin_rolling = $rolling_result;
            $user_info->save();
        }
    }

    public function recommends()
    {


    }
}
