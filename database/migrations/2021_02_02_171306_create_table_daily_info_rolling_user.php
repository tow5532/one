<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableDailyInfoRollingUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_info_rolling_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('search_date');
            $table->string('rolling_cd')->comment('정산 스케줄링 시 구분값');
            $table->longText('user_arr')->comment('json 데이타');
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
        Schema::dropIfExists('daily_info_rolling_user');
    }
}
