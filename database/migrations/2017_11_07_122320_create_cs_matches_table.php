<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_matches', function(Blueprint $t){
            $t->increments('id');
            $t->integer('player_1_id')->unsigned();
            $t->foreign('player_1_id')->references('id')->on('cs_players')->onDelete('cascade');
            $t->integer('player_2_id')->unsigned();
            $t->foreign('player_2_id')->references('id')->on('cs_players')->onDelete('cascade');
            $t->string('stage');
            $t->datetime('datetime');
            $t->string('status');
            $t->string('room');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_matches');
    }
}
