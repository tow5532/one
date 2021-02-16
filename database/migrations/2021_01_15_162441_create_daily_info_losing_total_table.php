<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyInfoLosingTotalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_info_losing_total', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->date('search_date');

            $table->unsignedBigInteger('user_id')->comment('users 테이블 시퀀스');
            $table->string('username')->comment('회원 아이디');
            $table->string('user_role')->comment('회원 등급');

            $table->string('total_deposit')->default('0')->comment('회원 기간 충전금액 합산');
            $table->string('total_refund')->default('0')->comment('회원 기간 출금액 합산');
            $table->string('total_point')->default('0')->comment('총보유 포인트 합산');
            $table->string('total_term_point')->default('0')->comment('정산기간에 등록된 보유 포인트');
            $table->string('total_past_point')->default('0')->comment('이전 정산 총 보유 포인트');
            $table->string('user_losing_total')->default('0')->comment('회원들 루징 금액 총엑');

            $table->string('commission')->comment('루징 수수료율 commission 테이블정보');

            $table->string('commission_final')->comment('나눠준 수수율을 제외하고 남은 수수료');

            $table->string('total_losing')->comment('총 루징 루징');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_info_losing_total', function (Blueprint $table) {
            $table->dropForeign('daily_info_losing_total_user_id_foreign');
        });

        Schema::dropIfExists('daily_info_losing_total');
    }
}
