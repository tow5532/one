<?php

namespace App\Admin\Controllers;

use App\DailyinfoMaster;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DailyinfoMasterController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Master Sales';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DailyinfoMaster());

        $grid->disableCreateButton();
        //$grid->disableFilter();
        $grid->disableActions();
        $grid->filter(function ($filter){
            $filter->disableIdFilter();
        });
        $grid->batchActions(function ($batchActions){
            $batchActions->disableDelete();
        });

        //$grid->setTitle('<b>asdfasdfsdafsdaf</b>');
        $grid->model()->orderBy('id', 'desc');

        $grid->column('search_date', trans('admin.sale_master.date'))->style('width:170px;text-align:right;');


        $grid->column('payment', trans('admin.sale_master.payment'))->display(function ($payment){
            return number_format($payment);
        })->style('width:170px;text-align:right;');

        $grid->column('transfer', trans('admin.sale_master.transfer'))->display(function ($transfer){
            return number_format($transfer);
        })->style('width:170px;text-align:right;');
        $grid->column('balance', trans('admin.sale_master.balancing'))->display(function ($balance){
            return number_format($balance);
        })->style('width:170px;text-align:right;');

        $grid->column('regular', trans('admin.sale_master.regular'))->display(function ($reqular){
            return number_format($reqular);
        })->style('width:170px;text-align:right;');
        $grid->column('bonus', trans('admin.sale_master.bonus'))->display(function ($bonus){
            return number_format($bonus);
        })->style('width:170px;text-align:right;');
        $grid->column('total', trans('admin.sale_master.total'))->display(function ($total){
            return number_format($total);
        })->style('width:170px;text-align:right;');

        $grid->column('normal_master_fee', trans('admin.sale_master.normal_master_fee'))->display(function ($normal_master_fee){
            return number_format($normal_master_fee);
        })->style('width:170px;text-align:right;');
        $grid->column('normal_company_fee', trans('admin.sale_master.normal_company_fee'))->display(function ($normal_company_fee){
            return number_format($normal_company_fee);
        })->style('width:170px;text-align:right;');
        $grid->column('normal_jack_fee', trans('admin.sale_master.normal_jack'))->display(function ($normal_jack_fee){
            return number_format($normal_jack_fee);
        })->style('width:170px;text-align:right;');

        $grid->column('sit_master_fee', trans('admin.sale_master.sit_master_fee'))->display(function ($sit_master_fee){
            return number_format($sit_master_fee);
        })->style('width:170px;text-align:right;');
        $grid->column('sit_company_fee', trans('admin.sale_master.sit_company_fee'))->display(function ($sit_company_fee){
            return number_format($sit_company_fee);
        })->style('width:170px;text-align:right;');

        $grid->column('tour_master_fee', trans('admin.sale_master.tour_master_fee'))->display(function ($tour_master_fee){
            return number_format($tour_master_fee);
        })->style('width:170px;text-align:right;');
        $grid->column('tour_company_fee', trans('admin.sale_master.tour_company_fee'))->display(function ($tour_company_fee){
            return number_format($tour_company_fee);
        })->style('width:170px;text-align:right;');

        $grid->column('company_chip_payment', trans('admin.sale_master.company_chip_payment'))->display(function ($company_chip_payment){
            return number_format($company_chip_payment);
        })->style('width:170px;text-align:right;');
        $grid->column('company_chip_reload', trans('admin.sale_master.company_chip_reload'))->display(function ($company_chip_reload){
            return number_format($company_chip_reload);
        })->style('width:170px;text-align:right;');
        $grid->column('company_chip_total', trans('admin.sale_master.company_chip_total'))->display(function ($company_chip_total){
            return number_format($company_chip_total);
        })->style('width:170px;text-align:right;');

        $grid->column('user_chips', trans('admin.sale_master.user_chips'))->display(function ($user_chips){
            return number_format($user_chips);
        })->style('width:170px;text-align:right;');
        $grid->column('user_safe', trans('admin.sale_master.user_safe'))->display(function ($user_safe){
            return number_format($user_safe);
        })->style('width:170px;text-align:right;');
        $grid->column('user_deposit', trans('admin.sale_master.user_deposit'))->display(function ($user_deposit){
            return number_format($user_deposit);
        })->style('width:170px;text-align:right;');
       // $grid->column('created_at', trans('admin.sale_master.total'));
        //$grid->column('updated_at', __('Updated at'));

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
        /*$show = new Show(DailyinfoMaster::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('search_date', __('Search date'));
        $show->field('payment', __('Payment'));
        $show->field('transfer', __('Transfer'));
        $show->field('balance', __('Balance'));
        $show->field('regular', __('Regular'));
        $show->field('bonus', __('Bonus'));
        $show->field('total', __('Total'));
        $show->field('normal_master_fee', __('Normal master fee'));
        $show->field('normal_company_fee', __('Normal company fee'));
        $show->field('normal_jack_fee', __('Normal jack fee'));
        $show->field('sit_master_fee', __('Sit master fee'));
        $show->field('sit_company_fee', __('Sit company fee'));
        $show->field('tour_master_fee', __('Tour master fee'));
        $show->field('tour_company_fee', __('Tour company fee'));
        $show->field('company_chip_payment', __('Company chip payment'));
        $show->field('company_chip_reload', __('Company chip reload'));
        $show->field('company_chip_total', __('Company chip total'));
        $show->field('user_chips', __('User chips'));
        $show->field('user_safe', __('User safe'));
        $show->field('user_deposit', __('User deposit'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;*/
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        /*$form = new Form(new DailyinfoMaster());

        $form->date('search_date', __('Search date'))->default(date('Y-m-d'));
        $form->text('payment', __('Payment'));
        $form->text('transfer', __('Transfer'));
        $form->text('balance', __('Balance'));
        $form->text('regular', __('Regular'));
        $form->text('bonus', __('Bonus'));
        $form->text('total', __('Total'));
        $form->text('normal_master_fee', __('Normal master fee'));
        $form->text('normal_company_fee', __('Normal company fee'));
        $form->text('normal_jack_fee', __('Normal jack fee'));
        $form->text('sit_master_fee', __('Sit master fee'));
        $form->text('sit_company_fee', __('Sit company fee'));
        $form->text('tour_master_fee', __('Tour master fee'));
        $form->text('tour_company_fee', __('Tour company fee'));
        $form->text('company_chip_payment', __('Company chip payment'));
        $form->text('company_chip_reload', __('Company chip reload'));
        $form->text('company_chip_total', __('Company chip total'));
        $form->text('user_chips', __('User chips'));
        $form->text('user_safe', __('User safe'));
        $form->text('user_deposit', __('User deposit'));

        return $form;*/
    }
}
