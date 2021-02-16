<?php

namespace App\Admin\Controllers;

use App\GameAccount;
use App\GameSitLog;
use App\GamesitMember;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class GameLogSitController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Sit&Go Game Logs';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GameSitLog());
        $grid->model()->orderBy('idx', 'desc');

        $grid->disableCreateButton();
        //$grid->disableFilter();
        //$grid->disableRowSelector();
        //$grid->disableExport();
        //$grid->disableActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
            $filter->expand();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->column('idx', trans('admin.game_log_sit.no'));

        $grid->column('Start_Date', trans('admin.game_log_sit.start_date'))->display(function ($Start_Date){
            //return Carbon::parse(strtotime($Start_Date))->format('Y-m-d H:i:s');
            $date = date("Y-m-d H:i:s", strtotime( $Start_Date ) );
            return $date;
        });

        $grid->column('End_Date', trans('admin.game_log_sit.end_date'))->display(function ($End_Date){
            if ($End_Date === '0'){
                return '';
            }
            //return Carbon::parse(strtotime($End_Date))->format('Y-m-d H:i:s');
            $date = date("Y-m-d H:i:s", strtotime( $End_Date ) );
            return $date;
        });

        $grid->column('BuyIn_Money', trans('admin.game_log_sit.buy_money'))->display(function ($BuyIn_Money){
           return number_format($BuyIn_Money);
        });

        $grid->column('House_Edge', trans('admin.game_log_sit.hose_edge'))->display(function ($House_Edge){
            return number_format($House_Edge);
        });

        $grid->column(trans('admin.game_log_sit.users'))->display(function (){
            $members = GamesitMember::where('Start_Date', $this->Start_Date)
                ->where('GameUniqueID', $this->GameUniqueID)
                ->where('GameRulesID', $this->GameRulesID)->get();
            $list = array();
            foreach ($members as $member){
                $users_id = GameAccount::where('AccountIDx', $member->AccountUniqueid)->first();
                array_push($list, $users_id->AccountID);
            }
            $collection = collect($list);
            return $collection->implode( ', ');
        });

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
        $show = new Show(GameSitLog::findOrFail($id));
        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            //$tools->disableList();
            $tools->disableDelete();
        });

        $show->field('Start_Date', trans('admin.game_log_sit.start_date'))->as(function ($Start_Date){
            //return Carbon::parse(strtotime($Start_Date))->format('Y-m-d H:i:s');
            return date("Y-m-d H:i:s", strtotime( $Start_Date ) );
        });

        $show->field('End_Date', trans('admin.game_log_sit.end_date'))->as(function ($End_Date){
            if ($End_Date === '0'){
                return 'ing';
            }
            $date = date("Y-m-d H:i:s", strtotime( $End_Date ) );
            //return Carbon::parse(strtotime($End_Date))->format('Y-m-d H:i:s');
        });

        $show->logs('Detail Logs', function ($logs){
            //filter
            $logs->disableCreateButton();
            //$grid->disableFilter();
            $logs->disableRowSelector();
            $logs->disableExport();
            $logs->disableActions();
            $logs->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
            });
            $logs->filter(function ($filter){
                $filter->disableIdFilter();
                $filter->expand();
                $filter->where(function ($query){
                    $query->where('gamelog', 'like', "%{$this->input}%");
                }, 'ID or Nick');
            });

            $logs->resource('/admin/log-moneys');
            $logs->model()->orderBy('idx', 'desc');

            $logs->idx(trans('admin.game_log.no'));

            $logs->column(trans('admin.game_log.user_id'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user_info .=   $v['userid']  . '<br>';
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.user_nick'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user_info .=   $v['nickname']  . '<br>';
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.position'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user_info .=   $v['seat']  . '<br>';
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.commu_card'))->display(function (){
                $data = json_decode($this->gamelog, true);

                $handArray = $data['community_card'];
                $handArray = str_replace(array('S', 'D', 'C', 'H'), array('♤', '◇', '♧', '♡'), $handArray);

                return $handArray;
            });

            $logs->column(trans('admin.game_log.hand_card'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){

                    $handArray = $v['hand'];
                    $handArray = str_replace(array('S', 'D', 'C', 'H'), array('♤', '◇', '♧', '♡'), $handArray);

                    $user_info .=   $handArray  . '<br>';
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.title'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user_info .=   $v['handrank']  . '<br>';
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.rank'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user_info .=   $v['ranking']  . '<br>';
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.start_stack'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user_info .=   number_format($v['begin_money'])  . '<br>';
                }
                return $user_info;
            });


            $logs->column(trans('admin.game_log.betting_amount'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user_info .=  number_format($v['bet_money'])  . '<br>';
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.change_amount'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user_info .=  number_format((int)$v['change_money'])  . '<br>';
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.current_Stack'))->display(function (){
                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user_info .=  number_format($v['current_money'])  . '<br>';
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.final_rank'))->display(function (){

                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user = GameAccount::where('AccountID', $v['userid'])->first();
                    $info = GamesitMember::where('Start_Date', $this->Contest_Start_Date)
                        ->where('GameUniqueID', $this->GameUniqueID)
                        ->where('GameRulesID', $this->GameRulesID)
                        ->where('AccountUniqueid' , $user->AccountIDx)
                        ->first();
                    if ((int)$info->isgiveup > 0){
                        $user_info .=  $info->Rank.'(giveup)' . '<br>';
                    } else {
                        $user_info .=  $info->Rank . '<br>';
                    }
                }
                return $user_info;
            });

            $logs->column(trans('admin.game_log.prize'))->display(function (){

                $data = json_decode($this->gamelog, true);
                $user_info = '';
                foreach ($data['userinfo'] as $i => $v){
                    $user = GameAccount::where('AccountID', $v['userid'])->first();
                    $info = GamesitMember::where('Start_Date', $this->Contest_Start_Date)
                        ->where('GameUniqueID', $this->GameUniqueID)
                        ->where('GameRulesID', $this->GameRulesID)
                        ->where('AccountUniqueid' , $user->AccountIDx)
                        ->first();
                    $user_info .=  number_format($info->Prize) . '<br>';
                }
                return $user_info;
            });




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
