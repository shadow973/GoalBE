<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCsGroupPlayerAddAdvancesToNextStage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cs_group_player', function(Blueprint $t){
            $t->boolean('advances_to_next_stage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cs_group_player', function(Blueprint $t){
            $t->dropColumn('advances_to_next_stage');
        });
    }
}
