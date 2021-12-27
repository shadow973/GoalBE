<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slides', function(Blueprint $t){
            $t->increments('id');
            $t->integer('article_id')->unsigned()->nullable();
            $t->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $t->string('image')->nullable();
            $t->string('link')->nullable();
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
        Schema::dropIfExists('slides');
    }
}
