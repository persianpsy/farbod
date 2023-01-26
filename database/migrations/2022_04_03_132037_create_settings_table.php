<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('language')->default('fa');
            $table->string('gateway')->default('zarinpal');
            $table->string('otp')->default('sms');
            $table->longText('rules')->nullable();
            $table->longText('en_rules')->nullable();
            $table->foreignId('logo')->nullable()->constrained('media');
            $table->json('sliders')->nullable();
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
        Schema::dropIfExists('settings');
    }
}
