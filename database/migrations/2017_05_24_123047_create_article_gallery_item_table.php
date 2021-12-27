<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleGalleryItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_gallery_item', function(Blueprint $t){
            $t->integer('article_id')->unsigned();
            $t->integer('gallery_item_id')->unsigned();
            $t->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            $t->foreign('gallery_item_id')->references('id')->on('gallery_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_gallery_item');
    }
}
