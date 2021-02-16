<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Rank;
use App\GameAccount;
use App\GamesitMember;
use App\GameTourLog;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class GameTourLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Tournament Game Logs';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GameTourLog());
        $grid->model()->orderBy('idx', 'desc');

        $grid->disableCreateButton();
        //$grid->disableFilter();
       // $grid->disableRowSelector();
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


        $grid->column('idx', trans('admin.game_log_tour.no'));

        $grid->column('Start_Date', trans('admin.game_log_tour.start_date'))->display(function ($Start_Date){
            return Carbon::parse(strtotime($Start_Date))->format('Y-m-d H:i:s');
        });

        $grid->column('End_Date', trans('admin.game_log_tour.end_date'))->display(function ($End_Date){
            if ($End_Date === '0'){
                return null;
            }
            return Carbon::parse(strtotime($End_Date))->format('Y-m-d H:i:s');
        });

        $grid->column('BuyIn_Money', trans('admin.game_log_tour.buy_money'))->display(function ($BuyIn_Money){
            return number_format($BuyIn_Money);
        });

        $grid->column('House_Edge', trans('admin.game_log_tour.hose_edge'))->display(function ($House_Edge){
            return number_format($House_Edge);
        });

        $grid->column('Player_Count', trans('admin.game_log_tour.users'))->display(function ($Player_Count){
            return number_format($Player_Count);
        });

        $grid->column(trans('admin.game_log_tour.rank'))->expand(function ($model){
            $ranks = $model->ranks()->orderBy('Rank', 'ASC')->get()->map(function ($rank){
                $rank->Prize = number_format($rank->Prize);
                $rank->user_id = GameAccount::where('AccountIDx', $rank->AccountUniqueID)->value('AccountID');
               return $rank->only(['Rank', 'Prize','PlayerName', 'user_id']);
            });
            return new Table([
                trans('admin.game_log_tour.rank'),
                trans('admin.game_log_tour.prize'),
                trans('admin.game_log_tour.user_nick'),
                trans('admin.game_log_tour.user_id')
            ], $ranks->toArray());
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
        $show = new Show(GameTourLog::findOrFail($id));
        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            //$tools->disableList();
            $tools->disableDelete();
        });

        $show->field('Start_Date', trans('admin.game_log_tour.start_date'))->as(function ($Start_Date){
            return Carbon::parse(strtotime($Start_Date))->format('Y-m-d H:i:s');
        });

        $show->field('End_Date', trans('admin.game_log_tour.end_date'))->as(function ($End_Date){
            if ($End_Date === '0'){
                return null;
            }
            return Carbon::parse(strtotime($End_Date))->format('Y-m-d H:i:s');
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
        $form = new Form(new GameTourLog());

        return $form;
    }
}
