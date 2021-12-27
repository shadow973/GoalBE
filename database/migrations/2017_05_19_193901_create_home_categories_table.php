<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_categories', function(Blueprint $t){
            $t->increments('id');
            $t->integer('category_id')->unsigned();
            $t->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $t->integer('order')->default(100);
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
        Schema::dropIfExists('home_categories');
    }
}
