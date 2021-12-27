<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaCupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fa_cup_players', function(Blueprint $t){
            $t->increments('id');
            $t->string('name');
        });

        Schema::create('fa_cup_groups', function(Blueprint $t){
            $t->increments('id');
            $t->string('name');
            $t->string('stage');
            $t->datetime('date');
            $t->string('next_group_ids');
            $t->string('room');
        });

        Schema::create('fa_cup_group_player', function(Blueprint $t){
            $t->increments('id');
            $t->integer('group_id')->unsigned();
            $t->integer('player_id')->unsigned();
            $t->integer('order')->default(1000);
            $t->boolean('advances_to_next_stage')->nullable();
            $t->foreign('group_id')->references('id')->on('fa_cup_groups')->onDelete('cascade');
            $t->foreign('player_id')->references('id')->on('fa_cup_players')->onDelete('cascade');
        });

        Schema::create('fa_cup_matches', function(Blueprint $t){
            $t->increments('id');
            $t->integer('player_1_id')->unsigned();
            $t->integer('player_2_id')->unsigned();
            $t->string('stage');
            $t->datetime('datetime');
            $t->string('status');
            $t->string('room');
            $t->integer('player_1_score')->unsigned()->nullable();
            $t->integer('player_2_score')->unsigned()->nullable();
            $t->foreign('player_1_id')->references('id')->on('fa_cup_players')->onDelete('cascade');
            $t->foreign('player_2_id')->references('id')->on('fa_cup_players')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fa_cup_matches');
        Schema::dropIfExists('fa_cup_group_player');
        Schema::dropIfExists('fa_cup_groups');
        Schema::dropIfExists('fa_cup_players');
    }
}
