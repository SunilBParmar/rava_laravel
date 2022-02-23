<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryWorkoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_workout', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index('category_id');
            $table->unsignedBigInteger('workout_id')->index('workout_id');
            $table->timestamps();
        });

        (new \Database\Seeders\CategorySeeder())->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_workout');
    }
}
