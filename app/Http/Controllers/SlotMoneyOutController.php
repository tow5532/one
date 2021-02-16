<?php

namespace App\Http\Controllers;

use App\GameInfo;
use App\GameSafeMoneyLog;
use App\Point;
use App\SlotGameMoneyOut;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SlotMoneyOutController extends Controller
{
    public function index()
    {
        $gameInfo   = GameInfo::where(['name'=>'slot'])->first();
        $outs       = SlotGameMoneyOut::where( 'flag' , '0')->get();

        echo '총 ' .$outs->count() . ' 개 남음 ';
        echo '<br><br>';

        foreach ($outs as $out)
        {
            echo $out->Aid;
            echo '  |  ';
            echo $out->SaveMoney;

            //회원 아이디 조회
            $user = User::where('account_id', $out->Aid)->first();

            if ($user) {
                echo '  |  ';
                echo $user->username;

                //로그테이블에 등록
                $logs = new GameSafeMoneyLog;
                $logs->user_id = $user->id;
                $logs->safer_id = $out->Idx;
                $logs->safe_money = $out->SaveMoney;
                $logs->save();

                //가져온후 게임 디비에 정보 업데이트
                $out->Flag = '1';
                $out->UpdateDate = Carbon::now();
                $out->save();

                //포인트 테이블 등록
                $userOrigPoint  = Point::where(['user_id' => $user->id, 'use_point' => '0'])->sum('point');
                $userUsePoint   = Point::where(['user_id' => $user->id, 'point' => '0'])->sum('use_point');
                $userPoint      = $userOrigPoint - $userUsePoint;

                $point = new Point;
                $point->user_id = $user->id;
                $point->po_content = 'send_web';
                $point->point = $out->SaveMoney;
                $point->use_point = '0';
                $point->mb_point = $userPoint;
                $point->game_id = $gameInfo->id;
                $point->save();
            }

            echo '<br>';
        }
    }
}
