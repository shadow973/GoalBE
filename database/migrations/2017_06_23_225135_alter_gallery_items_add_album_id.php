<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGalleryItemsAddAlbumId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gallery_items', function(Blueprint $t){
            $t->integer('album_id')->unsigned()->nullable();
            $t->foreign('album_id')->references('id')->on('gallery_albums')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gallery_items', function(Blueprint $t){
            $t->dropForeign('gallery_items_album_id_foreign');
            $t->dropColumn('album_id');
        });
    }
}
