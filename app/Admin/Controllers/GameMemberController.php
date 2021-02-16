<?php

namespace App\Admin\Controllers;

use App\GameMember;
use App\Point;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class GameMemberController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Game Member';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        //최초 회원 테이블에서 하부 회원 조회
        $userTable = config('admin.database.users_table');

        //어드민 계정이 아닐경우 조건문 을 추가 한다.
        if (!Admin::user()->isRole('administrator') && !Admin::user()->isRole('master')) {
            $recommend = DB::table('recommends')->where('user_id', Admin::user()->id)->first();

            if ($recommend->step1_id === Admin::user()->id) {
                $step_col = 'step1_id';
            }
            if ($recommend->step2_id === Admin::user()->id) {
                $step_col = 'step2_id';
            }
            if ($recommend->step3_id === Admin::user()->id) {
                $step_col = 'step3_id';
            }
            if ($recommend->step4_id === Admin::user()->id) {
                $step_col = 'step4_id';
            }
            if ($recommend->step5_id === Admin::user()->id) {
                $step_col = 'step5_id';
            }

            $users = DB::table($userTable)
                ->rightJoin('recommends', $userTable.'.id', '=', 'recommends.user_id')
                ->whereNotNull($userTable. '.account_id')
                ->where('recommends.' . $step_col, '=', Admin::user()->id)
                ->select('users.account_id')->get();
        }
        else {
            $users = DB::table($userTable)
                ->rightJoin('recommends', $userTable.'.id', '=', 'recommends.user_id')
                ->whereNotNull($userTable. '.account_id')
                ->select('users.account_id')->get();
        }

        $userArray = array();
        foreach ($users as $user)
        {
            array_push($userArray, $user->account_id);
        }

        $grid = new Grid(new GameMember());
        $grid->model()->whereIn('AccountUniqueid', $userArray);
        $grid->model()->orderBy('LastLogin', 'desc');
        //$grid->model()->has('account');


        //filter
        $grid->disableCreateButton();
        //$grid->disableTools();
        //$grid->disableFilter();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->where(function ($query) {
                $query->whereHas('account', function ($query) {
                    $query->where('AccountID', 'like', "%{$this->input}%");
                });
                $query->orWhere('PlayerName', 'like', "%{$this->input}%");
            }, 'ID or Nick');
        });


        $grid->account()->AccountID(trans('admin.game_member.user_id'));
        //$grid->column('AccountID');

        $grid->column('PlayerName', trans('admin.game_member.Nick'));

        $grid->column('Flag', trans('admin.game_member.flag'))->display(function ($Flag){
            $file = Storage::get('/public/flag.json');
            $data = json_decode($file, true);
            foreach ($data['tobigca_holdem_flag_code'] as $i => $v){
                if ((int)$v['flag_id'] === (int)$Flag){
                    return $v['country_name'];
                }
            }
        });

        /*$grid->user(trans('admin.game_member.point'))->display(function ($user){
            //포인트
            $h_in_point = 0;
            foreach ($user as $id) {
                $h_add_cnt    = Point::where('user_id', $id)->where('use_point', '=', '0')->sum('point');
                $h_minus_cnt  = Point::where('user_id', $id)->where('point', '=', '0')->sum('use_point');
                $h_in_point   = $h_add_cnt - $h_minus_cnt;
            }

        });*/
        $grid->column('AccountUniqueid', trans('admin.game_member.point'))->display(function ($AccountUniqueid){
            $user = User::where('account_id', $AccountUniqueid)->first();

            $h_add_cnt    = Point::where('user_id', $user->id)->where('use_point', '=', '0')->sum('point');
            $h_minus_cnt  = Point::where('user_id', $user->id)->where('point', '=', '0')->sum('use_point');
            $h_in_point   = $h_add_cnt - $h_minus_cnt;
            return number_format($h_in_point);
        });

        $grid->column('Have_Money', trans('admin.game_member.chips'))->display(function ($Have_Money){
            return chageDemit($Have_Money);
        });

        $grid->column('BiggestPot_Money', trans('admin.game_member.best_money'))->display(function ($BiggestPot_Money){
            return  chageDemit($BiggestPot_Money);
        });

        $grid->column('CreateDate', trans('admin.game_member.created_at'))->display(function ($CreateDate){
            //$time = Carbon::parse($CreateDate)->timestamp;
            //return Carbon::parse($time)->timezone('Asia/Ulaanbaatar')->toDateTimeString();
            //return changeTimeArea($CreateDate);
            return $CreateDate;
        })->sortable();

        $grid->column('LastLogin', trans('admin.game_member.last_login'))->display(function ($LastLogin){
            //$time = Carbon::parse($LastLogin)->timestamp;
            //return Carbon::parse($time)->timezone('Asia/Ulaanbaatar')->toDateTimeString();
            //return changeTimeArea($LastLogin);
            return $LastLogin;
        })->sortable();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(GameMember::findOrFail($id));


        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
            });

        $show->field('account', trans('admin.game_member.user_id'))->as(function ($account){
            return $account['AccountID'];
        });

        $show->field('PlayerName', trans('admin.game_member.Nick'));

        $show->field('Flag', trans('admin.game_member.flag'))->as(function ($Flag){
            $file = Storage::get('/public/flag.json');
            $data = json_decode($file, true);
            foreach ($data['tobigca_holdem_flag_code'] as $i => $v){
                if ((int)$v['flag_id'] === (int)$Flag){
                    return $v['country_name'];
                }
            }
        });

        $show->field('user', trans('admin.game_member.point'))->as(function ($user){
            $h_add_cnt    = Point::where('user_id', $user['id'])->where('use_point', '=', '0')->sum('point');
            $h_minus_cnt  = Point::where('user_id', $user['id'])->where('point', '=', '0')->sum('use_point');
            $h_in_point   = $h_add_cnt - $h_minus_cnt;
            return number_format($h_in_point);
        });


        $show->field('Have_Money', trans('admin.game_member.chips'))->as(function ($Have_Money){
            return chageDemit($Have_Money);
        });


        $show->field('BiggestPot_Money', trans('admin.game_member.best_money'))->as(function ($BiggestPot_Money){
            return  chageDemit($BiggestPot_Money);
        });

        $show->field('CreateDate', trans('admin.game_member.created_at'))->as(function ($CreateDate){
            //$time = Carbon::parse($CreateDate)->timestamp;
            //return Carbon::parse($time)->timezone('Asia/Ulaanbaatar')->toDateTimeString();
            //return changeTimeArea($CreateDate);
            return $CreateDate;
        });

        $show->field('LastLogin', trans('admin.game_member.last_login'))->as(function ($LastLogin){
            //$time = Carbon::parse($LastLogin)->timestamp;
            //return Carbon::parse($time)->timezone('Asia/Ulaanbaatar')->toDateTimeString();
            //return changeTimeArea($LastLogin);
            return $LastLogin;
        });

        $show->divider();

        $show->field(trans('admin.game_member.folded_during'))->unescape()->as(function (){

            $totalPlaycnt = (int)$this->Total_PlayCount;
            $free = 0;
            $flop = 0;
            $turn = 0;
            $river = 0;

            if ($totalPlaycnt > 0) {
                $free = (int)$this->PreFlop_FoldCount / $totalPlaycnt * 100;
                $flop = (int)$this->Flop_FoldCount / $totalPlaycnt * 100;
                $turn = (int)$this->Turn_FoldCount / $totalPlaycnt * 100;
                $river = (int)$this->River_FoldCount / $totalPlaycnt * 100;
            }

            $headers = [
                trans('admin.game_member.free'),
                trans('admin.game_member.flap'),
                trans('admin.game_member.turn'),
                trans('admin.game_member.river')
            ];
            $rows = [
                [(int)$free . '%', (int)$flop.'%', (int)$turn.'%', (int)$river.'%'],
            ];
            $table = new Table($headers, $rows);
            return $table->render();
        });

        $show->field('Win_Count', trans('admin.game_member.win_cnt'))->as(function ($Win_Count){
            return number_format($Win_Count);
        });

        $show->field('BestHand', trans('admin.game_member.best_hands'))->as(function ($BestHand){
            $hands = '';
            $best_array = explode(',', $BestHand);
            $file_path = storage_path('app/public/best_hands.txt');

            foreach ($best_array as $val){

                $file = fopen($file_path, "r");
                while(!feof($file)) {
                    $str = fgets($file);
                    $str_array = explode('|', $str);
                    $str_num    = $str_array[0];
                    $str_hand   = $str_array[1];

                    if ((int)$val === (int)$str_num){
                        $hands .=  $str_hand . ',';
                    }
                }
                fclose($file);
            }
            return rtrim($hands, ',');
        });

        $show->logmoneys('Money Logs', function ($logmoneys){

            $logmoneys->resource('/admin/game-logs');

            $logmoneys->batchActions(function ($batchActions){
                $batchActions->disableDelete();
            });
            $logmoneys->disableRowSelector();
            $logmoneys->disableColumnSelector();

            $logmoneys->model()->orderBy('idx', 'desc');

            //$logmoneys->idx(trans('admin.game_member.last_login'));
            $logmoneys->Own_Money(trans('admin.game_member.own_amount'))->display(function ($Own_Money){
                return number_format($Own_Money);
            });
            $logmoneys->Fluctuation_money(trans('admin.game_member.chg_amount'))->display(function ($Fluctuation_money){
                return number_format($Fluctuation_money);
            });
            $logmoneys->currently_money(trans('admin.game_member.current_amount'))->display(function ($currently_money){
                return number_format($currently_money);
            });
            $logmoneys->Fluctuation_reason(trans('admin.game_member.chg_reason'))->display(function ($Fluctuation_reason){

                if (app()->getLocale() === 'ko'){
                    $reason_array = array(
                        0 => '알수 없음',
                        1 => '칩 구매',
                        2 => '일반 게임 플레이',
                        3 => '싯앤고 게임 플레이',
                        4 => '토너먼트 게임 플레이',
                        5 => '무료 칩',
                        6 => '구글에서 구매',
                        7 => '쿠폰 사용',
                        8 => '금고로 머니 이동',
                        9 => '칩으로 아이템 구매',
                    );
                } else {
                    $reason_array = array(
                        0 => 'Unknown',
                        1 => 'Buy chips',
                        2 => 'Normal Game Play',
                        3 => 'Sit&Go Game Play',
                        4 => 'tournament Game Play',
                        5 => 'Free chips',
                        6 => 'Buy Google',
                        7 => 'Use Coupon',
                        8 => 'Moving money to the safe',
                        9 => 'Buying items with chips',
                    );
                }
                return $reason_array[$Fluctuation_reason];
            });
            $logmoneys->Fluctuation_date(trans('admin.game_member.chg_date'))->display(function ($Fluctuation_date){
                return $Fluctuation_date;
            });


            $logmoneys->disableCreateButton();
            $logmoneys->disableFilter();
            $logmoneys->disableActions();
            $logmoneys->disableExport();
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

    }

}
