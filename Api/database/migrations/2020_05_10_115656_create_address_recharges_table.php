<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAddressRechargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_recharges', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->bigInteger('uid')->index();
            $table->integer('wid')->index()->comment('充值币种id');
            $table->string('code', 20)->comment('充值币种code');
            $table->string('address')->comment('充值地址');
            $table->string('hash')->comment('哈希');
            $table->decimal('amount', 20, 8)->default(0)->comment('充值数量');
            $table->tinyInteger('status')->default(1)->comment('充值状态 1充值中 2充值成功');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `recharges` comment '地址充值表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recharges');
    }
}
