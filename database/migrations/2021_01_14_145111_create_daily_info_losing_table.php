<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyInfoLosingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_info_losing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('search_date');
            $table->unsignedBigInteger('user_id')->comment('users 테이블 시퀀스');
            $table->string('username')->comment('회원 아이디');
            $table->string('total_deposit')->default('0')->comment('회원 기간 충전금액');
            $table->string('total_refund')->default('0')->comment('회원 기간 출금액');
            $table->string('total_point')->default('0')->comment('총보유 포인트');
            $table->string('term_point')->default('0')->comment('정산기간에 등록된 보유 포인트');
            $table->string('past_user_point')->default('0')->comment('이전 정산 총 보유 포인트');

            $table->string('user_losing')->default('0')->comment('회원 루징 금액');

            $table->unsignedBigInteger('store_id')->comment('매장 계정 회원 시퀀스');
            $table->string('store_commission')->comment('매장 루징 수수료율 commission 테이블정보');
            $table->string('store_losing')->comment('매장 루징');

            $table->unsignedBigInteger('dist_id')->comment('총판 계정 회원 시퀀스');
            $table->string('dist_commission')->comment('총판 루징 수수료율 commission 테이블정보');
            $table->string('dist_commission_final')->comment('매장 수수율을 제외하고 남은 수수료');
            $table->string('dist_losing')->comment('총판 루징');

            $table->unsignedBigInteger('sub_id')->comment('부본 계정 회원 시퀀스');
            $table->string('sub_commission')->comment('부본 루징 수수료율 commission 테이블정보');
            $table->string('sub_commission_final')->comment('총판 수수율을 제외하고 남은 수수료');
            $table->string('sub_losing')->comment('부본 루징');

            $table->unsignedBigInteger('com_id')->comment('본사 계정 회원 시퀀스');
            $table->string('com_commission')->comment('본사 루징 수수료율 commission 테이블정보');
            $table->string('com_commission_final')->comment('본사 수수율을 제외하고 남은 수수료');
            $table->string('com_losing')->comment('본사 루징');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('store_id')->references('id')->on('users');
            $table->foreign('dist_id')->references('id')->on('users');
            $table->foreign('sub_id')->references('id')->on('users');
            $table->foreign('com_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_info_losing', function (Blueprint $table) {
            $table->dropForeign('daily_info_losing_user_id_foreign');
            $table->dropForeign('daily_info_losing_store_id_foreign');
            $table->dropForeign('daily_info_losing_dist_id_foreign');
            $table->dropForeign('daily_info_losing_sub_id_foreign');
            $table->dropForeign('daily_info_losing_com_id_foreign');
        });
        Schema::dropIfExists('daily_info_losing');
    }
}
