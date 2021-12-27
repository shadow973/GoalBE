<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCsMatchesAddScores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cs_matches', function(Blueprint $t){
            $t->integer('player_1_score')->unsigned()->nullable();
            $t->integer('player_2_score')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cs_matches', function(Blueprint $t){
            $t->dropColumn('player_1_score');
            $t->dropColumn('player_2_score');
        });
    }
}
