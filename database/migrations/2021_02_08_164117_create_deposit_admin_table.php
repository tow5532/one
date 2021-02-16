<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_admin', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('users 테이블 시퀀스');
            $table->unsignedBigInteger('setp_id');
            $table->string('amount')->comment('신청수량');
            $table->string('bank', '100')->nullable();
            $table->string('account', '100')->nullable();
            $table->string('holder', '100')->nullable();
            $table->string('phone', '100')->nullable();
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
        Schema::table('deposit_admin', function (Blueprint $table) {
            $table->dropForeign('deposit_admin_user_id_foreign');
        });
        Schema::dropIfExists('deposit_admin');
    }
}
