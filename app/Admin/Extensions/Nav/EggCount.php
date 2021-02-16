<?php

namespace App\Admin\Extensions\Nav;

use App\HeadquarterLog;
use App\User;
use Encore\Admin\Facades\Admin;

class EggCount
{
    public function __toString()
    {
        //알 수량
        $add_cnt    = HeadquarterLog::where('user_id', Admin::user()->id)->where('use_point', '=', '0')->sum('point');
        $minus_cnt  = HeadquarterLog::where('user_id', Admin::user()->id)->where('point', '=', '0')->sum('use_point');
        $in_point   = $add_cnt - $minus_cnt;
        if ($in_point < 0){
            $in_point = 0;
        }
        $egg_amount = number_format($in_point);

        //루징 포인트 합계
        $losing_amount = number_format(Admin::user()->losing_cnt);

        //롤링 포인트 합계
        $rolling        = User::find(Admin::user()->id);
        $rolling_amount = number_format($rolling->admin_rolling);

        return <<<HTML
        <li>
            <a href="#">
            <i class="fa fa-money"></i>
              <span>알 수량 : $egg_amount</span>
            </a>
        </li>
        <li>
            <a href="#">
            <i class="fa fa-money"></i>
              <span>롤링 수익 : $rolling_amount</span>
            </a>
        </li>
        <li>
            <a href="#">
            <i class="fa fa-money"></i>
              <span>루징 수익 : $losing_amount</span>
            </a>
        </li>
<!--<li>
    <a href="#">
      <i class="fa fa-envelope-o"></i>
      <span class="label label-success">4</span>
    </a>
</li>

<li>
    <a href="#">
      <i class="fa fa-bell-o"></i>
      <span class="label label-warning">7</span>
    </a>
</li>

<li>
    <a href="#">
      <i class="fa fa-flag-o"></i>
      <span class="label label-danger">9</span>
    </a>
</li>-->
HTML;
    }
}
