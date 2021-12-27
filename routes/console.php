<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('parseleagues', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseLeagues();
})->describe('Parse leagues');

Artisan::command('parsecountries', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseCountries();
})->describe('Parse Countries');

Artisan::command('parseToday', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseToDay();
})->describe('Parse Countries');

Artisan::command('parseNow', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseNow();
})->describe('Parse Countries');

Artisan::command('parseMonth', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseMonth();
})->describe('Parse Countries');

Artisan::command('parseLastMonth', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseLastMonth();
})->describe('Parse Countries');

Artisan::command('parseTodayTeams', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseTodayTeams();
})->describe('Parse Today Teams');

Artisan::command('parseTeamsByMatches', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseTeamsByMatches();
})->describe('Parse Teams');

Artisan::command('parseTodayTeamsPlayers', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseTodayTeamsPlayers();
})->describe('Parse TodayTeamsPlayers');

Artisan::command('parseTeamsPlayers', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseTeamsPlayers();
})->describe('Parse TodayTeamsPlayers');


Artisan::command('parseStandings', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseStandings();
})->describe('Parse Standings');

Artisan::command('parsetest', function () {
    app('App\Http\Controllers\Cron\ApiController')->getMatches_test();
})->describe('Parse Countries');


Artisan::command('generatecflags', function () {
    app('App\Http\Controllers\Cron\ApiController')->generateCountryFlags();
})->describe('Parse Countries');

Artisan::command('parseSessionStats', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseSessionStats();
})->describe('Parse Session Stats');

Artisan::command('parseBySeasonId', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseBySeasonId();
})->describe('Parse parseBySeasonId');

Artisan::command('parseRounds', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseRounds();
})->describe('Parse rounds');

Artisan::command('parseSeasons', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseSeasons();
})->describe('Parse seasons');

Artisan::command('parseStages', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseStages();
})->describe('Parse stages');

Artisan::command('parseSeasonStats', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseSeasonStats();
})->describe('Parse season stats');

Artisan::command('parseTeamStatsBySeason', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseTeamStatsBySeason();
})->describe('Parse team stats by season');

Artisan::command('parseTeamStatsByLeague', function () {
    app('App\Http\Controllers\Cron\ApiController')->parseTeamStatsByLeague();
})->describe('Parse team stats by leagues');

Artisan::command('parseLeagueTeams', function () {
    app('App\Http\Controllers\Cron\ApiController')->getTeamsByLeague();
})->describe('Parse teams by leagues');


/*
 * Data Transfer ...
 */

Artisan::command('trnstest', function () {
    //app('App\Http\Controllers\DataTransfer\ConsoleController')->test();
})->describe('Simple desc');

Artisan::command('trnscats', function () {
    //app('App\Http\Controllers\DataTransfer\ConsoleController')->transferCategories();
})->describe('Simple desc');

Artisan::command('trnsusers', function () {
    //app('App\Http\Controllers\DataTransfer\ConsoleController')->transferUsers();
})->describe('Simple desc');

Artisan::command('trnspost', function () {
    //app('App\Http\Controllers\DataTransfer\ConsoleController')->transferPost();
})->describe('Simple desc');


Artisan::command('slugConvert', function () {
    app('App\Http\Controllers\Cron\TestController')->convertSlug();
})->describe('Simple desc');

Artisan::command('slugConvertCategory', function () {
    app('App\Http\Controllers\Cron\TestController')->convertSlugCategory();
})->describe('Simple desc');

Artisan::command('convertSlugTeams', function () {
    app('App\Http\Controllers\Cron\TestController')->convertSlugTeams();
})->describe('Simple desc');

Artisan::command('convertSlugLeagues', function () {
    app('App\Http\Controllers\Cron\TestController')->convertSlugLeagues();
})->describe('Simple desc');

Artisan::command('convertSlugPlayers', function () {
    app('App\Http\Controllers\Cron\TestController')->convertSlugPlayers();
})->describe('Simple desc');


/*
 * Site Parser ...
 */


Artisan::command('footballParse', function () {
    app('App\Http\Controllers\Cron\FootballController')->parse();
})->describe('Parse footballParse');

Artisan::command('football24Parse', function () {
    app('App\Http\Controllers\Cron\Football24Controller')->parse();
})->describe('Parse Football24Controller');

Artisan::command('sportParse', function () {
    app('App\Http\Controllers\Cron\SportController')->parse();
})->describe('Parse sportParse');

Artisan::command('generateSitemaps', function () {
    app('App\Http\Controllers\SitemapController')->generateAll();
})->describe('Simple desc');
