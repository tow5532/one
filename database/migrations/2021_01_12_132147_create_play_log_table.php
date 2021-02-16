<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('play_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('game_id')->comment('game_info 테이블 시퀀스');
            $table->unsignedBigInteger('user_id')->comment('users 테이블 시퀀스');
            $table->unsignedBigInteger('account_id')->comment('게임계정 시퀀스');
            $table->unsignedBigInteger('log_id')->comment('게임로그테이블 시퀀스');
            $table->string('game_no')->comment('슬롯게임종류');
            $table->string('game_srl')->comment('게임라운드');
            $table->string('start_balance')->comment('게임시작시 들고 있는 금액');
            $table->string('betting_money')->comment('베팅금액');
            $table->string('win_money')->comment('승리했을경우 총 금액');
            $table->string('profit')->comment('잃은금액 : 음수, 땃을때 금액: 양수');
            $table->string('end_balance')->comment('게임 끝나고 방나올때 가지고 있는 금액');
            $table->dateTime('game_date')->comment('로그 등록 시간');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('game_id')->references('id')->on('game_info')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('play_log', function (Blueprint $table) {
            $table->dropForeign('play_log_user_id_foreign');
            $table->dropForeign('play_log_game_id_foreign');
        });

        Schema::dropIfExists('play_log');
    }
}
