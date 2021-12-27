<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategorySubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_subscriptions', function(Blueprint $t){
            $t->integer('category_id')->unsigned();
            $t->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
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
        Schema::dropIfExists('category_subscriptions');
    }
}
