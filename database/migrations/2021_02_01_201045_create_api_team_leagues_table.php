<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiTeamLeaguesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_team_leagues', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_id');
            $table->integer('league_id');
            $table->integer('season_id');
            $table->longText('stats')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_team_leagues');
    }
}
