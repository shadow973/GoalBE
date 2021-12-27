<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_views', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('article_id')->unsigned();
            $t->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $t->timestamp('datetime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_views');
    }
}
