<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->longText('degree')->nullable();
            $table->longText('en_degree')->nullable();
            $table->longText('image')->nullable();
            $table->longText('experience')->nullable();//experience
            $table->longText('en_experience')->nullable();//experience
            $table->longText('aboutme')->nullable();
            $table->longText('en_aboutme')->nullable();
            $table->decimal('cost_toman',14,0)->nullable();
            $table->decimal('cost_dollar',14,0)->nullable();
            $table->decimal('commission',14,0)->nullable();
            $table->decimal('en_commission',14,0)->nullable();
            $table->integer('time_to_visit')->default(10);
            $table->longText('desc')->nullable();
            $table->longText('en_desc')->nullable();
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
        Schema::dropIfExists('staff');
    }
}
