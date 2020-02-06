<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_no');
            $table->string('sold_to');
            $table->string('address');
            $table->string('bus_style');
            $table->dateTime('date');
            $table->string('terms');
            $table->string('po_no');
            $table->bigInteger('updated_by');
            $table->double('total_amount');
            $table->double('amount_paid');
            $table->unsignedBigInteger('bank_trans_id')->default(0);
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
        Schema::dropIfExists('sales');
    }
}
