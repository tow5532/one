<?php

namespace App\Http\Controllers;

use App\User;
use App\ProfitChange;
use Illuminate\Http\Request;

class DailyProfitController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        //추가로 하부내에 수수료율을 수정한 이가 있으면 업데이트 해준다.
        $profit = ProfitChange::where('check_update', 'N')->get();
        foreach ($profit as $row)
        {
            $user = User::find($row->user_id);
            $user->profit = $row->chg_profit;
            $user->save();
        }
        ProfitChange::where('check_update', 'N')->update(['check_update' => 'Y']);
    }
}
