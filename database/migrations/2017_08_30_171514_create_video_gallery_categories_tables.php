<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kalnoy\Nestedset\NestedSet;

class CreateVideoGalleryCategoriesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_gallery_categories', function(Blueprint $t){
            $t->increments('id');
            $t->string('title');
            NestedSet::columns($t);
            $t->timestamps();
        });

        Schema::create('video_gallery_category__gallery_item', function(Blueprint $t){
            $t->integer('gallery_item_id')->unsigned();
            $t->integer('vgc_id')->unsigned();
            $t->foreign('gallery_item_id')->references('id')->on('gallery_items')->onDelete('cascade');
            $t->foreign('vgc_id')->references('id')->on('video_gallery_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_gallery_category__gallery_item');
        Schema::dropIfExists('video_gallery_categories');
    }
}
