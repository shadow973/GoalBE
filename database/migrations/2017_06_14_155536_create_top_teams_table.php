<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_teams', function(Blueprint $t){
            $t->increments('id');
            $t->integer('tag_id')->unsigned();
            $t->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $t->integer('order')->default(1000);
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
        Schema::dropIfExists('top_teams');
    }
}
