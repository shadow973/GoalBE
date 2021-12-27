<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function(Blueprint $t){
            $t->increments('id');
            $t->date('date')->nullable();
            $t->integer('player_tag_id')->unsigned();
            $t->foreign('player_tag_id')->references('id')->on('tags')->onDelete('cascade');
            $t->integer('from_team_tag_id')->unsigned();
            $t->foreign('from_team_tag_id')->references('id')->on('tags')->onDelete('cascade');
            $t->integer('to_team_tag_id')->unsigned();
            $t->foreign('to_team_tag_id')->references('id')->on('tags')->onDelete('cascade');
            $t->string('amount')->nullable();
            $t->string('type')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfers');
    }
}
