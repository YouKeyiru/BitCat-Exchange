<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('income_out', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid')->index('uid');
            $table->decimal('amount', 20, 6)->default(0)->comment('提取数量');
            $table->decimal('surplus', 20, 6)->default(0)->comment('结余');
            $table->tinyInteger('status')->default(1)->comment('1 待审核 2 通过 3 拒绝');
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
        Schema::dropIfExists('income_out');
    }
}
