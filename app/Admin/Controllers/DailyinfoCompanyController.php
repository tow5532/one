<?php

namespace App\Admin\Controllers;

use App\DailyinfoCompany;
use App\DailyinfoMaster;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class DailyinfoCompanyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Headquarter Sales';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DailyinfoCompany());

        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            //$actions->disableView();
        });
        //$grid->disableFilter();
        //$grid->disableActions();
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        $grid->model()->orderBy('id', 'desc');

        /*$grid->header(function ($query) {

            $yesterday          = Carbon::yesterday();
            $yesterdayDate      = $yesterday->toDateString();

            $total_payment          = $query->sum('total_payment');
            $total_refund           = $query->sum('total_refund');
            $company_chip_total     = $query->select('company_chip_total')->first();
            $company_chip_payment   = $query->sum('company_chip_payment');
            $company_chip_reload    = $query->sum('company_chip_reload');
            $user_chips             = $query->wheredate('search_date', $yesterdayDate)->select('user_chips')->first();
            $user_safe              = $query->sum('user_safe');
            $user_deposit           = $query->sum('user_deposit');
            $normal_company_fee     = $query->sum('normal_company_fee');
            $tour_company_fee       = $query->sum('tour_company_fee');
            $sit_company_fee        = $query->sum('sit_company_fee');

            if (Admin::user()->inRoles(['administrator', 'master'])) {
                $headers = [
                    trans('admin.sale_company.total_user_payment'),
                    trans('admin.sale_company.total_user_exchange'),
                    trans('admin.sale_company.total_egg'),
                    //trans('admin.sale_company.payment'),
                    trans('admin.sale_company.total_reload'),
                    trans('admin.sale_company.total_user_chips'),
                    //trans('admin.sale_company.total_user_safe'),
                    trans('admin.sale_company.total_user_deposit'),
                    trans('admin.sale_company.total_normal_fee'),
                    trans('admin.sale_company.total_tour_fee'),
                    trans('admin.sale_company.total_sit_fee'),
                ];
                $rows = [
                    [
                        number_format($total_payment),
                        number_format($total_refund),
                        number_format($company_chip_total['company_chip_total']),
                        //$company_chip_payment,
                        number_format($company_chip_reload),
                        number_format($user_chips['user_chips']),
                        //number_format($user_safe),
                        number_format($user_deposit),
                        number_format($normal_company_fee),
                        number_format($tour_company_fee),
                        number_format($sit_company_fee)
                    ]
                ];
            }
             else {
                 $headers = [
                     trans('admin.sale_company.total_user_payment'),
                     trans('admin.sale_company.total_user_exchange'),
                     trans('admin.sale_company.reload'),
                     trans('admin.sale_company.total_user_chips'),
                     //trans('admin.sale_company.total_user_safe'),
                     trans('admin.sale_company.total_user_deposit'),
                     trans('admin.sale_company.normal_fee'),
                     trans('admin.sale_company.tour_fee'),
                     trans('admin.sale_company.sit_fee'),
                 ];
                 $rows = [
                     [
                         number_format($total_payment),
                         number_format($total_refund),
                         number_format($company_chip_reload),
                         number_format($user_chips['user_chips']),
                         //number_format($user_safe),
                         number_format($user_deposit),
                         number_format($normal_company_fee),
                         number_format($tour_company_fee),
                         number_format($sit_company_fee)
                     ]
                 ];
             }

            $table = new Table($headers, $rows);

            return $table->render();
        });*/

        //Date
        $grid->column('search_date', trans('admin.sale_company.date'));

        //user payment
        $grid->column('total_payment', trans('admin.sale_company.user_payment'))->display(function ($total_payment){
            return number_format($total_payment);
        });

        //user withdrawals
        $grid->column('total_refund', trans('admin.sale_company.user_exchange'))->display(function ($total_refund) {
            return number_format($total_refund);
        });

        //total eggs
        if (Admin::user()->inRoles(['administrator', 'master'])) {
            $grid->column('company_chip_total', trans('admin.sale_company.total_egg'))->display(function ($company_chip_total) {
                return number_format($company_chip_total);
            });
        }

        //user chips
        $grid->column('user_chips', trans('admin.sale_company.total_user_chips'))->display(function ($user_chips){
            return number_format($user_chips);
        });

        //user sun point
        $grid->column('user_deposit', trans('admin.sale_company.user_deposit'))->display(function ($user_deposit){
            return number_format($user_deposit);
        });

        //user free sun point
        $grid->column('user_free_point', trans('admin.sale_company.user_free_point'))->display(function ($user_free_point){
            return number_format($user_free_point);
        });

        //우측 3열의 합
        $grid->column('total_fee', trans('admin.sale_company.total_fee'))->display(function ($total_fee){
            return number_format($total_fee);
        });

        //잭팟 합산
        $grid->column(trans('admin.sale_company.jackpot'))->display(function (){
            return number_format($this->normal_jack_fee);
        });

        //본사 수수료 합산
        $grid->column('company_total_fee', trans('admin.sale_company.company_total_fee'))->display(function ($company_total_fee){
            return number_format($company_total_fee);
        });

        //마스터 수수료 합산
        $grid->column('master_total_fee', trans('admin.sale_company.master_total_fee'))->display(function ($master_total_fee){
            return number_format($master_total_fee);
        });

        //게임 아이템 에 소모된 게임머니 합산
        $grid->column('game_item_money', trans('admin.sale_company.fluctuation_money'))->display(function ($game_item_money){
            return number_format($game_item_money);
        });

        /* // 잿팟
         $grid->column('normal_jack_fee', trans('admin.sale_company.jackpot'))->display(function ($normal_jack_fee){
             //return $normal_jack_fee;
             return number_format($normal_jack_fee);
         });

         //본사 노멀 수수료
         $grid->column('normal_company_fee', trans('admin.sale_company.normal_fee'))->display(function ($normal_company_fee){
             return number_format($normal_company_fee);
         });

         //마스터 노멀 수수료
         $grid->column('normal_master_fee', trans('admin.sale_company.master_normal_fee'))->display(function ($normal_master_fee){
             return number_format($normal_master_fee);
         });

         //본사 토너먼트 수수료
         $grid->column('tour_company_fee', trans('admin.sale_company.tour_fee'))->display(function ($tour_company_fee){
             return number_format($tour_company_fee);
         });

         //마스터 토너먼트 수수료
         $grid->column('tour_master_fee', trans('admin.sale_company.master_tour_fee'))->display(function ($tour_master_fee){
             return number_format($tour_master_fee);
         });

         //본사 싯앤고 수수료
         $grid->column('sit_company_fee', trans('admin.sale_company.sit_fee'))->display(function ($sit_company_fee){
             return number_format($sit_company_fee);
         });

         //마스터 싯앤고 수수료
         $grid->column('sit_master_fee', trans('admin.sale_company.master_sit_fee'))->display(function ($sit_master_fee){
             return number_format($sit_master_fee);
         });*/

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
        $show = new Show(DailyinfoCompany::findOrFail($id));

        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                // $tools->disableList();
                $tools->disableDelete();
            });



        $show->field('search_date', trans('admin.sale_company.date'));

        //user payment
        $show->field('total_payment', trans('admin.sale_company.user_payment'))->as(function ($total_payment){
            return number_format($total_payment);
        });

        //user withdrawals
        $show->field('total_refund', trans('admin.sale_company.user_exchange'))->as(function ($total_refund) {
            return number_format($total_refund);
        });

        //total eggs
        if (Admin::user()->inRoles(['administrator', 'master'])) {
            $show->field('company_chip_total', trans('admin.sale_company.total_egg'))->as(function ($company_chip_total) {
                return number_format($company_chip_total);
            });
        }

        //user chips
        $show->field('user_chips', trans('admin.sale_company.total_user_chips'))->as(function ($user_chips){
            return number_format($user_chips);
        });

        //user sun point
        $show->field('user_deposit', trans('admin.sale_company.user_deposit'))->as(function ($user_deposit){
            return number_format($user_deposit);
        });

        //user free sun point
        $show->field('user_free_point', trans('admin.sale_company.user_free_point'))->as(function ($user_free_point){
            return number_format($user_free_point);
        });

        $show->divider();

        //우측 3열의 합
        $show->field('total_fee', trans('admin.sale_company.total_fee'))->as(function ($total_fee){
            return number_format($total_fee);
        });

        //잭팟 합산
        $show->field(trans('admin.sale_company.jackpot'))->as(function (){
            return number_format($this->normal_jack_fee);
        });

        //본사 수수료 합산
        $show->field('company_total_fee', trans('admin.sale_company.company_total_fee'))->as(function ($company_total_fee){
            return number_format($company_total_fee);
        });

        //마스터 수수료 합산
        $show->field('master_total_fee', trans('admin.sale_company.master_total_fee'))->as(function ($master_total_fee){
            return number_format($master_total_fee);
        });

        //게임 아이템 에 소모된 게임머니 합산
        $show->field('game_item_money', trans('admin.sale_company.fluctuation_money'))->as(function ($game_item_money){
            return number_format($game_item_money);
        });

        $show->divider();

        // 잿팟
        $show->field('normal_jack_fee', trans('admin.sale_company.jackpot'))->as(function ($normal_jack_fee){
            //return $normal_jack_fee;
            return number_format($normal_jack_fee);
        });

        //본사 노멀 수수료
        $show->field('normal_company_fee', trans('admin.sale_company.normal_fee'))->as(function ($normal_company_fee){
            return number_format($normal_company_fee);
        });

        //마스터 노멀 수수료
        $show->field('normal_master_fee', trans('admin.sale_company.master_normal_fee'))->as(function ($normal_master_fee){
            return number_format($normal_master_fee);
        });

        $show->divider();

        //본사 토너먼트 수수료
        $show->field('tour_company_fee', trans('admin.sale_company.tour_fee'))->as(function ($tour_company_fee){
            return number_format($tour_company_fee);
        });

        //마스터 토너먼트 수수료
        $show->field('tour_master_fee', trans('admin.sale_company.master_tour_fee'))->as(function ($tour_master_fee){
            return number_format($tour_master_fee);
        });

        $show->divider();

        //본사 싯앤고 수수료
        $show->field('sit_company_fee', trans('admin.sale_company.sit_fee'))->as(function ($sit_company_fee){
            return number_format($sit_company_fee);
        });

        //마스터 싯앤고 수수료
        $show->field('sit_master_fee', trans('admin.sale_company.master_sit_fee'))->as(function ($sit_master_fee){
            return number_format($sit_master_fee);
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
        /*$form = new Form(new DailyinfoCompany());

        $form->date('search_date', __('Search date'))->default(date('Y-m-d'));
        $form->text('total_payment', __('Total payment'));
        $form->text('total_refund', __('Total refund'));
        $form->text('company_chip_payment', __('Company chip payment'));
        $form->text('company_chip_reload', __('Company chip reload'));
        $form->text('company_chip_total', __('Company chip total'));
        $form->text('user_chips', __('User chips'));
        $form->text('user_safe', __('User safe'));
        $form->text('user_deposit', __('User deposit'));
        $form->text('normal_company_fee', __('Normal company fee'));
        $form->text('tour_company_fee', __('Tour company fee'));
        $form->text('sit_company_fee', __('Sit company fee'));
        $form->text('company_rev', __('Company rev'));

        return $form;*/
    }
}
