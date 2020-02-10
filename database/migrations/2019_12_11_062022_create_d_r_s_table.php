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
            $table->string('delivered_to');
            $table->string('address');
            $table->string('delivery_style');
            $table->dateTime('date');
            $table->dateTime('date_to_be_paid');
            $table->string('terms');
            $table->string('tin');
            $table->bigInteger('updated_by');
            $table->double('amount_received', 10, 2);
            $table->double('total_amount', 10, 2);
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
