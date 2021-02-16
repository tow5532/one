<?php

namespace App\Http\Controllers;

use App\GameInfo;
use App\PlayLog;
use App\SlotGameUserLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GetGameLogDataController extends Controller
{

    public $todayDate;
    public $isComplete;
    public $gameID;

    public function __construct()
    {
        //$today                  = Carbon::today();
        $today                  = Carbon::parse('2021-01-22');
        $this->todayDate        = $today->toDateString();

        $this->isComplete       = '0';

        $game = GameInfo::where('code', 'slot')->first();
        $this->gameID = $game->id;
    }

    function index()
    {
        //isComplete 값이 0 인것만 1개 조회
        $logs = SlotGameUserLog::where('IsComplete', $this->isComplete)
            ->whereDate('GameDate', '>=', $this->todayDate)
            ->orderBy('Idx', 'asc')
            ->take(30)->get();

        if ($logs->count() > 0) {
            foreach ($logs as $log)
            {
                //웹회원 조회
                $user = User::where('account_id', $log->Aid)->first();

                if ($user->count() > 0) {
                    $playLog = new PlayLog;
                    $playLog->game_id = $this->gameID;
                    $playLog->user_id = $user->id;
                    $playLog->account_id = $log->Aid;
                    $playLog->log_id = $log->Idx;
                    $playLog->game_no = $log->GameNo;
                    $playLog->game_srl = $log->GameSrl;
                    $playLog->start_balance = $log->StartBalance;
                    $playLog->betting_money = $log->BettingMoney;
                    $playLog->win_money = $log->WinMoney;
                    $playLog->profit = $log->Profit;
                    $playLog->end_balance = $log->EndBalance;
                    $playLog->game_date = $log->GameDate;
                    $playLog->save();

                    //해당 데이터의 베팅 액수를 해당 회원 테이블에 업데이트 해준다.
                    $user->slot_betting_amount += $log->BettingMoney;
                    $user->save();

                    //웹데이터 등록후 게임 디비에 isComplete = 1 로 업데이트
                    $log->IsComplete = '1';
                    $log->save();
                }
            }
        }
    }
}
