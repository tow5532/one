<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyInfoRollingResult extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_info_rolling_result', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rolling_id')->comment('조회한 daily_info_rolling_user 테이블 시퀀스');
            $table->unsignedBigInteger('user_id')->comment('users 테이블 시퀀스');
            $table->string('username')->comment('회원 아이디');
            $table->string('user_role')->comment('회원 등급');
            $table->string('total_betting')->default('0')->comment('daily_info_rolling_user 해당 유저의 총합');
            $table->string('refund_amount')->default('0')->comment('해당 회원의 출금 한 누적 수량');
            $table->string('rolling')->default('0')->comment('total_betting -  refund_amount = 롤링 배당 금액');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_info_rolling_result');
    }
}
