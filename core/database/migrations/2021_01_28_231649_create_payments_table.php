<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('transaction_id')->nullable();
            $table->string('ref_id')->nullable();
            $table->decimal('price',10,0);
            $table->string('token')->nullable();
            $table->string('model_type')->nullable();
            $table->string('model_id')->nullable();
            $table->smallInteger('type')->default(0);
            $table->smallInteger('status')->default(1); //1-register 2-in progress 3-cancel
            $table->string('gateway')->default('zarinpal');
            $table->softDeletes();
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
        Schema::dropIfExists('payments');
    }
}
