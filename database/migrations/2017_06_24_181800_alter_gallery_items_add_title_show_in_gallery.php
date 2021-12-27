<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGalleryItemsAddTitleShowInGallery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gallery_items', function(Blueprint $t){
            $t->string('title')->nullable();
            $t->boolean('show_in_video_gallery')->default(false);
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
            $t->dropColumn('title');
            $t->dropColumn('show_in_video_gallery');
        });
    }
}
