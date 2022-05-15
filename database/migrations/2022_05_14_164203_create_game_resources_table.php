<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_resources', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid();
            $table->text('board');
            $table->integer('score_x');
            $table->integer('score_y');
            $table->integer('current_turn');
            $table->integer('victory')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_resources');
    }
};
