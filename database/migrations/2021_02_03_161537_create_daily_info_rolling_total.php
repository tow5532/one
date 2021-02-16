<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyInfoRollingTotal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_info_rolling_total', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('users 테이블 시퀀스');
            $table->string('username')->comment('회원 아이디');
            $table->string('user_role')->comment('회원 등급');
            $table->string('total_betting')->default('0')->comment('하부 일반회원 베팅머니 총합');
            $table->string('refund_amount')->default('0')->comment('해당 회원의 출금 한 누적 수량');
            $table->string('commission')->default('0')->comment('해당회원의 롤링수수료율');
            $table->string('commission_final')->default('0')->comment('해당 회원의 하부 수수료율을 제외한 실제 수수료율');
            $table->string('rolling')->default('0')->comment('롤링 배당 금액');
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
        Schema::table('daily_info_rolling_total', function (Blueprint $table) {
            $table->dropForeign('daily_info_rolling_total_user_id_foreign');
        });

        Schema::dropIfExists('daily_info_rolling_total');
    }
}
