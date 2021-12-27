<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableArticleAnchorsDropArticleId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('article_anchors', function(Blueprint $t){
            $t->dropForeign(['article_id']);
            $t->dropColumn(['article_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('article_anchors', function(Blueprint $t) {
            $t->unsignedInteger('article_id')->nullable();
            $t->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
        });
    }
}
