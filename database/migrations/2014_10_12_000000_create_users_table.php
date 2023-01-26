<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('en_first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('en_last_name')->nullable();
            $table->string('cellphone');
            $table->string('location');
            $table->string('password')->nullable();
            $table->string('national_code')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->decimal('phone',11,0)->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_verified_at')->default('0');
            $table->text('address')->nullable();
            $table->foreignId('birth_county')->nullable()->constrained('counties');
            $table->foreignId('residence')->nullable()->constrained('counties');
            $table->string('birthday')->nullable();
            $table->string('father_name')->nullable();
            $table->longText('desc')->nullable();
            $table->longText('en_desc')->nullable();
            $table->string('auth_code')->nullable();
            $table->bigInteger('skyroom_id')->nullable();
            $table->softDeletes();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
