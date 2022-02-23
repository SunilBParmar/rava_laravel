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
            $table->string('email', 256);
            $table->string('password', 256);
            $table->enum('role', ['trainer', 'sportsman', 'admin']);
            $table->string('first_name', 256)->nullable();
            $table->string('last_name', 256)->nullable();
            $table->date('birthday')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->unsignedDouble('weight')->nullable();
            $table->unsignedDouble('height')->nullable();
            $table->unsignedInteger('fitness_level')->nullable();
            $table->text('fitness_goals')->nullable();
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
