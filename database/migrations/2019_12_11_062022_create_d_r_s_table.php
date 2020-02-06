<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDRSTable extends Migration
{
    /**
     * Run the migrations.
     *s
     * @return void
     */
    public function up()
    {
        Schema::create('deliveryReciepts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('dr_no');
            $table->unsignedBigInteger('si_id')->default(0);
            $table->string('delivered_to');
            $table->string('address');
            $table->string('delivery_style');
            $table->dateTime('date');
            $table->string('terms');
            $table->string('tin');
            $table->bigInteger('updated_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('d_r_s');
    }
}
