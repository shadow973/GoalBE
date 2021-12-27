<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterArticlesAddMainGalleryItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles', function(Blueprint $t){
            $t->integer('main_gallery_item_id')->unsigned()->nullable();
            $t->foreign('main_gallery_item_id')->references('id')->on('gallery_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function(Blueprint $t){
            $t->dropForeign('articles_main_gallery_item_id_foreign');
            $t->dropColumn('main_gallery_item_id');
        });
    }
}
