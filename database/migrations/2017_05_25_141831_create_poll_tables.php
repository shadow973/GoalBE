<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('polls', function(Blueprint $t){
            $t->increments('id');
            $t->string('question');
            $t->integer('user_id')->unsigned()->nullable();
            $t->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $t->timestamps();
        });

        Schema::create('poll_answers', function(Blueprint $t){
            $t->increments('id');
            $t->string('answer');
            $t->integer('poll_id')->unsigned();
            $t->foreign('poll_id')->references('id')->on('polls')->onDelete('cascade');
            $t->timestamps();
        });

        Schema::create('poll_answer_user', function(Blueprint $t){
            $t->integer('poll_answer_id')->unsigned();
            $t->foreign('poll_answer_id')->references('id')->on('poll_answers')->onDelete('cascade');
            $t->integer('user_id')->unsigned();
            $t->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poll_answer_user');
        Schema::dropIfExists('poll_answers');
        Schema::dropIfExists('polls');
    }
}
