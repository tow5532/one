<?php

namespace App\Admin\Controllers;

use App\GameLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class GameLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = ' Normal Game Logs';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GameLog());

        $grid->model()->where('channel', 'normal');
        $grid->model()->orderBy('idx', 'desc');

        //$grid->model()->where('gamelog->userinfo->userid', '=', 'ldhkms2');

        //filter
        $grid->disableCreateButton();
        //$grid->disableFilter();
       // $grid->disableRowSelector();
        //$grid->disableExport();
        $grid->disableActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->filter(function ($filter){
            $filter->disableIdFilter();
            $filter->expand();
           $filter->where(function ($query){
               $query->where('gamelog', 'like', "%{$this->input}%");
           }, 'ID or Nick');
        });



        $grid->idx(trans('admin.game_log.no'));

        $grid->column(trans('admin.game_log.date'))->display(function (){
            $data = json_decode($this->gamelog, true);
            return $data['begin_date'].' <br>~ '. $data['end_date'];
        });

        $grid->column(trans('admin.game_log.user_id'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {
                    $user_info .= $v['userid'] . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column(trans('admin.game_log.user_nick'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {
                    $user_info .= $v['nickname'] . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column(trans('admin.game_log.position'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {
                    $user_info .= $v['seat'] . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column(trans('admin.game_log.commu_card'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $handArray = $data['community_card'];
            $handArray = str_replace(array('S', 'D', 'C', 'H'), array('♤', '◇', '♧', '♡'), $handArray);

            return $handArray;
        });

        $grid->column(trans('admin.game_log.hand_card'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {

                    $handArray = $v['hand'];
                    $handArray = str_replace(array('S', 'D', 'C', 'H'), array('♤', '◇', '♧', '♡'), $handArray);

                    $user_info .= $handArray . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column(trans('admin.game_log.title'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {
                    $user_info .= $v['handrank'] . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column(trans('admin.game_log.rank'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {
                    $user_info .= $v['ranking'] . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column(trans('admin.game_log.begin_amount'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {
                    $user_info .= number_format($v['begin_money']) . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column(trans('admin.game_log.betting_amount'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {
                    $user_info .= number_format($v['bet_money']) . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column(trans('admin.game_log.change_amount'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {
                    $user_info .= number_format((int)$v['change_money']) . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column(trans('admin.game_log.current_amount'))->display(function (){
            $data = json_decode($this->gamelog, true);
            $user_info = '';
            if ($data['userinfo']) {
                foreach ($data['userinfo'] as $i => $v) {
                    $user_info .= number_format($v['current_money']) . '<br>';
                }
                return $user_info;
            }
        });

        $grid->column('fee', trans('admin.game_log.house_edge'))->display(function ($fee){
            return number_format($fee);
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
