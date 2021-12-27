<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_groups', function(Blueprint $t){
            $t->increments('id');
            $t->string('name');
            $t->string('stage');
            $t->date('date');
        });

        Schema::create('cs_group_player', function(Blueprint $t){
            $t->integer('group_id')->unsigned();
            $t->foreign('group_id')->references('id')->on('cs_groups')->onDelete('cascade');
            $t->integer('player_id')->unsigned();
            $t->foreign('player_id')->references('id')->on('cs_players')->onDelete('cascade');
            $t->integer('order')->default(1000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_group_player');
        Schema::dropIfExists('cs_groups');
    }
}
