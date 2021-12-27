<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function(Blueprint $t){
            $t->increments('id');
            $t->integer('article_id')->unsigned();
            $t->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $t->integer('user_id')->unsigned();
            $t->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $t->text('content');
            $t->timestamps();
        });

        Schema::create('replies', function(Blueprint $t){
            $t->increments('id');
            $t->integer('comment_id')->unsigned();
            $t->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
            $t->integer('user_id')->unsigned();
            $t->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $t->text('content');
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
        Schema::dropIfExists('replies');
        Schema::dropIfExists('comments');
    }
}
