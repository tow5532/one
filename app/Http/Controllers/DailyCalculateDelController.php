<?php

namespace App\Http\Controllers;

use App\DailyInfoUserRolling;
use App\DailyInfoUserRollingResult;
use App\DailyInfoUserRollingTotal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyCalculateDelController extends Controller
{
    public $delDate;

    public function __construct()
    {
        $yesterdayBefore        = Carbon::yesterday()->addDay(-2);
        $this->delDate          = $yesterdayBefore->toDateString();
    }

    public function start()
    {
        //Log::channel('calcul_rolling_del')->info('Del Date : '. $this->delDate);
        echo $this->delDate;

        //daily_info_rolling_user
        $dailyRollingUser = DailyInfoUserRolling::whereDate('search_date', '<', $this->delDate)->delete();

        //daily_info_rolling_total
        $dailyRollingTotal = DailyInfoUserRollingTotal::whereDate('created_at', '<', $this->delDate)->delete();

        //daily_info_rolling_result
        $dailyRollingResult = DailyInfoUserRollingResult::whereDate('created_at', '<', $this->delDate)->delete();
    }
}
