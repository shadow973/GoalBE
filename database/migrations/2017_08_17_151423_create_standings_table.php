<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStandingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leagues', function(Blueprint $t){
            $t->increments('id');
            $t->integer('category_id')->unsigned();
            $t->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $t->integer('order')->default(1000);
            $t->timestamps();
        });

        Schema::create('teams', function(Blueprint $t){
            $t->increments('id');
            $t->integer('tag_id')->unsigned();
            $t->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $t->integer('league_id')->unsigned();
            $t->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
            $t->integer('order')->default(1000);
            $t->integer('matches')->default(0);
            $t->integer('points')->default(0);
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
        Schema::dropIfExists('teams');
        Schema::dropIfExists('leagues');
    }
}
