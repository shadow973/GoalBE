<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTvProgramTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tv_programs', function(Blueprint $t){
            $t->increments('id');
            $t->integer('channel_id')->unsigned();
            $t->date('date');
            $t->unique(['channel_id', 'date']);
            $t->timestamps();
        });

        Schema::create('tv_program_items', function(Blueprint $t){
            $t->increments('id');
            $t->integer('tv_program_id')->unsigned();
            $t->foreign('tv_program_id')->references('id')->on('tv_programs')->onDelete('cascade');
            $t->string('time');
            $t->string('title');
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
        Schema::dropIfExists('tv_program_items');
        Schema::dropIfExists('tv_programs');
    }
}
