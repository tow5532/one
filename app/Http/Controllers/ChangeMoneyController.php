<?php

namespace App\Http\Controllers;

use App\HeadquarterLog;
use App\LosingPoint;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChangeMoneyController extends Controller
{
    public function change_rolling()
    {
        if (Admin::user()->id === null){
            return response()->json(array('success' => false, 'err_msg' => '로그인을 해야 합니다.'));
        }

        //해당 회원 롤링 조회
        $user = User::find(Admin::user()->id);
        $user_rolling = $user->admin_rolling;

        //다시 조회하여 수량이 없으면 오류
        if ($user_rolling <= 0){
            return response()->json(array('success' => false, 'err_msg' => '현재 남은 수량이 없습니다.'));
        }

        //잔여 포인트 검색
        $add_cnt    = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
        $minus_cnt  = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
        $in_point   = $add_cnt - $minus_cnt;

        //롤링 수량을 알로 전환
        $head = new HeadquarterLog;
        $head->user_id = Admin::user()->id;
        $head->po_content = 'change_rolling';
        $head->point = $user_rolling;
        $head->use_point = '0';
        $head->mb_point = $in_point;
        $head->save();

        //해당 회원의 롤링을 빼준다. 알은 더해준다.
        $user->egg_amount     += $user_rolling;
        $user->admin_rolling  -= $user_rolling;
        $user->save();

        return response()->json(array('success' => true, 'err_msg' => '성공적으로 ' . $user_rolling . ' 의 롤링 수익이 알로 변환 되었습니다.'));

    }

    public function change_losing()
    {
        if (Admin::user()->id === null){
            return response()->json(array('success' => false, 'err_msg' => '로그인을 해야 합니다.'));
        }

        //회원 루징 조회
        $user = User::find(Admin::user()->id);
        $losing_amount = (int)$user->losing_cnt;

        //다시 조회하여 수량이 없으면 오류
        if ($losing_amount <= 0){
            return response()->json(array('success' => false, 'err_msg' => '현재 남은 수량이 없습니다.'));
        }

        //잔여 포인트 검색
        $add_cnt    = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
        $minus_cnt  = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
        $in_point   = $add_cnt - $minus_cnt;

        //롤링 수량을 알로 전환
        $head = new HeadquarterLog;
        $head->user_id = Admin::user()->id;
        $head->po_content = 'change_losing';
        $head->point = $losing_amount;
        $head->use_point = '0';
        $head->mb_point = $in_point;
        $head->save();


        //해당 회원의 롤링을 빼준다., 알은 더해준다.
        $user->egg_amount += $losing_amount;
        $user->losing_cnt -= $losing_amount;
        $user->save();

        return response()->json(array('success' => true, 'err_msg' => '성공적으로 ' . $losing_amount . ' 의 루징 수익이 알로 변환 되었습니다.'));
    }
}
