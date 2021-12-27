<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalistCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journalist_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->date('date');
            $table->text('comment');
            $table->integer('journalist_id')->unsigned();
            $table->integer('content_manager_id')->unsigned()->nullable();
            $table->foreign('journalist_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('content_manager_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journalist_comments');
    }
}
