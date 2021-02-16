<?php

namespace App\Http\Controllers;

use App\Commission;
use App\DailyInfoLosing;
use App\DailyInfoLosingTotal;
use App\LosingPoint;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyCalculateTotalController extends Controller
{
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

    public function index($id)
    {
        Log::channel('calcul_losing_level')->info('check Date : '. $this->yesterdayDate. ' | category : '. $id);

        $where_query = '';

        if ($id === 'company'){
            $where_query = 'com_id';
        }
        elseif ($id === 'sub_company'){
            $where_query = 'sub_id';
        }
        elseif ($id === 'distributor'){
            $where_query = 'dist_id';
        }
        elseif ($id === 'store'){
            $where_query = 'store_id';
        }

        if ($id === null || $where_query === ''){
            exit;
        }


        $losings = DailyInfoLosing::where('search_date', $this->yesterdayDate)->get();
        //dd($losings);

        foreach ($losings as $losing)
        {
            echo '일반 계정명 : ' . $losing->username;
            echo '<br>';

            //해당 아이디를 조회하여 날짜별로 집계를 해준다.
            //해당 날짜 회원 정보 집계
            /*$total_deposit      = DailyInfoLosing::where('search_date', $losing->search_date)->where($where_query, $losing->$where_query)->sum('total_deposit');
            $total_refund       = DailyInfoLosing::where('search_date', $losing->search_date)->where($where_query, $losing->$where_query)->sum('total_refund');
            $total_point        = DailyInfoLosing::where('search_date', $losing->search_date)->where($where_query, $losing->$where_query)->sum('total_point');
            $term_point         = DailyInfoLosing::where('search_date', $losing->search_date)->where($where_query, $losing->$where_query)->sum('term_point');
            $past_user_point    = DailyInfoLosing::where('search_date', $losing->search_date)->where($where_query, $losing->$where_query)->sum('past_user_point');
            $total_user_losing  = DailyInfoLosing::where('search_date', $losing->search_date)->where($where_query, $losing->$where_query)->sum('user_losing');
            $admin_losing       = DailyInfoLosing::where('search_date', $losing->search_date)->where($where_query, $losing->$where_query)->sum('store_losing');*/

            $total_deposit      = $losing->total_deposit;
            $total_refund       = $losing->total_refund;
            $total_point        = $losing->total_point;
            $term_point         = $losing->term_point;
            $past_user_point    = $losing->past_user_point;
            $total_user_losing  = $losing->user_losing;
            $total_game_money   = $losing->total_game_money;


            $admin_losing = '';
            if ($id === 'company'){
                $admin_losing           = $losing->com_losing;
            }
            elseif ($id === 'sub_company'){
                $admin_losing           = $losing->sub_losing;
            }
            elseif ($id === 'distributor'){
                $admin_losing           = $losing->dist_losing;
            }
            elseif ($id === 'store'){
                $admin_losing           = $losing->store_losing;
            }


            //회원 아이디조회
            $user = User::where('id', $losing->$where_query)->first();

            //회원 등급 조회
            $user_grade     = DB::table('admin_role_users')->where('user_id', $user->id)->first();
            $user_role      = DB::table('admin_roles')->where('id', $user_grade->role_id)->first();

            //해당 등급 데이터가 있는지 확인
            $isTotal = DailyInfoLosingTotal::where('search_date', $losing->search_date)->where('user_id', $losing->$where_query)->first();
            if ($isTotal !== null){
                $isTotal->total_deposit += $total_deposit;
                $isTotal->total_refund += $total_refund;
                $isTotal->total_point += $total_point;
                $isTotal->total_term_point += $term_point;
                $isTotal->total_past_point += $past_user_point;
                $isTotal->user_losing_total += $total_user_losing;
                $isTotal->total_losing += $admin_losing;
                $isTotal->total_game_money += $total_game_money;
                $isTotal->save();
            } else {
                $store_insert = new DailyInfoLosingTotal;
                $store_insert->search_date = $losing->search_date;
                $store_insert->user_id = $losing->$where_query;
                $store_insert->username = $user->username;
                $store_insert->total_deposit = $total_deposit;
                $store_insert->total_refund = $total_refund;
                $store_insert->total_point = $total_point;
                $store_insert->total_term_point = $term_point;
                $store_insert->total_past_point = $past_user_point;
                $store_insert->user_losing_total = $total_user_losing;
                $store_insert->commission = $losing->store_commission;
                $store_insert->commission_final = $losing->store_commission;
                $store_insert->total_losing = $admin_losing;
                $store_insert->total_game_money = $total_game_money;
                $store_insert->user_role = $user_grade->role_id;
                $store_insert->level = $user_role->slug;
                $store_insert->save();
            }

            echo '받는 하부 계정 아이디 : ' . $user->username;
            echo '<br>';

        }

        //회원 일별 루프가 끝나고 나면, 위에 등록된  데이터를 다시 루프 돌면서 루징 수익을 넣어 준다.
        $totals = DailyInfoLosingTotal::where('search_date', $this->yesterdayDate)->where('level', $id)->get();

        foreach ($totals as $total)
        {
            //루징 수익 계산 0 이하면 0으로 판단 해야 한다.
            $losing_total   = $total->total_losing;
            $losing_revenue =  ($losing_total > 0) ? $losing_total : 0;

            //루징 수익 값 업데이트
            $total->total_losing_revenue = $losing_revenue;
            $total->save();

            //$losing_revenue 값을 루징 포인트 테이블에 등록
            // 0보다 크다면 등록
            if ($losing_revenue > 0) {
                $losingPoint = new LosingPoint;
                $losingPoint->user_id = $total->user_id;
                $losingPoint->losing_id = $total->id;
                $losingPoint->po_content = 'daily_cal';
                $losingPoint->point = $losing_revenue;
                $losingPoint->use_point = '0';
                $losingPoint->mb_point = '0';
                $losingPoint->cal_date = $this->yesterdayDate;
                $losingPoint->save();

                //루징 합계를 회원 테이블에 더해 준다.
                $user = User::find($total->user_id);
                $user->losing_cnt += $losing_revenue;
                $user->save();
            }
        }
    }

}

