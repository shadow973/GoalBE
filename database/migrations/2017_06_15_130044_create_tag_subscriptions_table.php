<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_subscriptions', function(Blueprint $t){
            $t->integer('tag_id')->unsigned();
            $t->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
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
        Schema::dropIfExists('tag_subscriptions');
    }
}
