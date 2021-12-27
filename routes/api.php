<?php

use App\Models\ApiTeams;
use Illuminate\Support\Facades\DB;

Route::get('users/export', 'UsersController@export');
Route::get('users/activate', 'UsersController@activateUser');
Route::get('users/me', 'UsersController@getAuthenticated');
Route::get('users/stats', 'UsersController@getStats');
Route::get('users/statistics', 'UsersController@getStatistics');

Route::get('users/{id}/articles', 'UsersController@getArticles');
Route::get('users/{id}/subscriptions', 'UsersController@getSubscriptions');
Route::get('users/{id}/comments', 'UsersController@getComments');
Route::get('my-comments', 'CommentsController@myComments');
Route::get('my-subscriptions', 'UsersController@mySubscriptions');

Route::post('users/update', 'UsersController@updateAuthenticated');
Route::post('users/change-password', 'UsersController@changePassword');

Route::post('register', 'UsersController@register');
Route::post('subscribe/category/{id}', 'UsersController@subscribeToCategory');
Route::post('subscribe/tag/{id}', 'UsersController@subscribeToTag');
Route::post('subscribe/team/{id}', 'UsersController@subscribeToTeam');
Route::post('subscribe/player/{id}', 'UsersController@subscribeToPlayer');

Route::patch('categories/reorder', 'CategoriesController@reorder');

Route::get('categories/export', 'CategoriesController@export');

Route::get('tags/export', 'TagsController@export');

Route::patch('home-categories/reorder', 'HomeCategoriesController@reorder');

Route::post('login/jwt', 'AuthController@logIn');
Route::post('login/refresh_token', 'AuthController@refreshToken');
Route::post('login/reset', 'AuthController@resetPassword');

Route::post('gallery/upload', 'GalleryController@upload');
Route::post('gallery/upload/sm-video', 'GalleryController@uploadSetantaMediaVideoData');
Route::post('gallery/{id}', 'GalleryController@patch'); // had some problems with PATCH requests and XHR that's why its POST

Route::post('polls/answer', 'PollsController@answer');

Route::patch('slides/reorder', 'SlidesController@reorder');

Route::post('tv-programs/{id}', 'TVProgramsController@update');

Route::patch('top-teams/reorder', 'TopTeamsController@reorder');

Route::get('comments', 'CommentsController@comments');
Route::get('comments/stats', 'CommentsController@getStats');
Route::get('comments/latest', 'CommentsController@latest');
Route::get('comments/item/{itemid}', 'CommentsController@commentsItem');
Route::post('comments/item/{itemid}', 'CommentsController@commentsItem');
Route::get('comments/delete/{itemid}', 'CommentsController@commentsDelete');
Route::post('comments/delete', 'CommentsController@commentsDeleteItems');

Route::get('articles/{articleId}/comments', 'CommentsController@index');
Route::post('articles/{articleId}/comments', 'CommentsController@store');
Route::patch('articles/{articleId}/comments/{commentId}', 'CommentsController@update');
Route::put('articles/{articleId}/comments/{commentId}', 'CommentsController@update');
Route::delete('articles/{articleId}/comments/{commentId}', 'CommentsController@destroy');

//match comments

Route::get('matchcomments', 'MatchCommentsController@comments');
Route::get('matchcomments/stats', 'MatchCommentsController@getStats');
Route::get('matchcomments/latest', 'MatchCommentsController@latest');
Route::get('matchcomments/item/{itemid}', 'MatchCommentsController@commentsItem');
Route::post('matchcomments/item/{itemid}', 'MatchCommentsController@commentsItem');
Route::get('matchcomments/delete/{itemid}', 'MatchCommentsController@commentsDelete');
Route::post('matchcomments/delete', 'MatchCommentsController@commentsDeleteItems');

Route::resource('match/{matchId}/comments', 'MatchCommentsController', [
    'except' => ['create', 'edit']
]);

Route::resource('match/{matchId}/comments/{commentId}/replies', 'MatchCommentRepliesController', [
    'except' => ['create', 'edit']
]);

Route::post('matchreplies/like/{repl_id}', 'MatchCommentRepliesController@like');
Route::post('matchreplies/dislike/{repl_id}', 'MatchCommentRepliesController@dislike');
Route::post('matchreplies/unlike/{repl_id}', 'MatchCommentRepliesController@unlike');


Route::post('matchcomments/like/{com_id}', 'MatchCommentsController@like');
Route::post('matchcomments/dislike/{com_id}', 'MatchCommentsController@dislike');
Route::post('matchcomments/unlike/{com_id}', 'MatchCommentsController@unlike');


Route::resource('articles/{articleId}/comments/{commentId}/replies', 'RepliesController', [
    'except' => ['create', 'edit', 'show']
]);

Route::post('replies/like/{repl_id}', 'RepliesController@like');
Route::post('replies/unlike/{repl_id}', 'RepliesController@unlike');
Route::post('comments/unlike/{com_id}', 'CommentsController@unlike');
Route::post('replies/dislike/{repl_id}', 'RepliesController@dislike');
Route::post('comments/like/{com_id}', 'CommentsController@like');
Route::post('comments/dislike/{com_id}', 'CommentsController@dislike');

Route::resource('roles', 'RolesController', [
    'only' => ['index']
]);

Route::resource('journalist-comments', 'JournalistCommentsController', [
    'except' => ['create', 'edit']
]);

Route::patch('standings/reorder', 'StandingsController@reorder');
Route::patch('standings/{id}/reorder', 'StandingsController@reorderTeams');




/*
 *Live Score
 */

Route::any('live-matches/','LiveScoreController@GetLiveMatches');
Route::any('live-matches/players/{id}','LiveScoreController@GetMatchPlayers');
Route::any('live-matches/player-stats/{id}','LiveScoreController@GetMatchPlayersStats');
Route::any('live-matches/players/{id}/{playerId}','LiveScoreController@GetMatchPlayerStats');
Route::any('live-matches/statistics/{id}','LiveScoreController@GetMatchStatistics');
Route::any('live-matches/info/{id}','LiveScoreController@GetMatchInfo');
Route::any('live-matches/highlights/{id}','LiveScoreController@GetMatchHighlights');
Route::any('live-matches/overview/{id}','LiveScoreController@GetMatchOverview');
Route::any('live-matches/overview-mobile/{id}','LiveScoreController@GetMatchOverviewMobile');
Route::any('live-matches/h2h/{id}','LiveScoreController@GetH2HMatches');
Route::any('live-matches/standings/{id}','LiveScoreController@GetMatchStandings');

Route::any('matches','LiveScoreController@GetMatches');
Route::any('match/{id}','LiveScoreController@GetMatches');
Route::post('matchrate/{id}','LiveScoreController@rateMatche');
Route::any('leagues','LiveScoreController@GetLeagues');
Route::any('leagues/country-leagues','LeagueController@GetCountryLeagues');
Route::get('league-icons/{leagueId}.png','LeagueController@GetLeagueIcon');
Route::any('leagues-mobile','LiveScoreController@GetStandingsMobile');
Route::any('leagues/articles/{id}','LiveScoreController@GetLeagueArticles');
Route::any('leagues/{id}','LiveScoreController@GetLeague');
Route::any('leagues/info/{id}','LiveScoreController@GetLeagueInfo');
Route::any('leagues/matches/{id}','LiveScoreController@GetLeagueMatches');
Route::any('leagues/matches_round/{id}','LiveScoreController@GetLeagueMatchesByRound');
Route::any('leagues/stats/{id}','LiveScoreController@GetLeaguesStats');
Route::any('teams','LiveScoreController@GetTeams');
Route::any('teamsbyleague/{league_id}','LiveScoreController@GetTeamsByLeague');
Route::any('teamsbyleague','LiveScoreController@GetTeamsByLeague');
Route::any('teams/top','LiveScoreController@GetTopTeams');
Route::any('teams/matches/{id}','LiveScoreController@GetTeamMatches');
Route::any('teams/data/{id}','LiveScoreController@GetTeamGeneralData');
Route::any('teams/players/{id}','LiveScoreController@GetTeamPlayers');
Route::any('teams/articles/{id}','LiveScoreController@GetTeamArticles');
Route::any('teams/stats/{id}','LiveScoreController@GetTeamStats');
Route::any('teams/league-stats/{teamId}/{leagueId}/{seasonId?}','LiveScoreController@GetTeamStatsByLeague');
Route::any('teams/leagues/{teamId}','LiveScoreController@GetTeamLeagues');
Route::any('teams/overall-stats/{teamId}','LiveScoreController@GetTeamOverallStat');
Route::any('teams/standings/{id}','LiveScoreController@GetTeamStandings');
Route::any('teams/transfers/{id}','LiveScoreController@GetTeamTransfers');
Route::any('teams/info/{id}','LiveScoreController@GetTeamInfo');
Route::any('teams/search/{s}','LiveScoreController@GetTeamsSearch');
Route::any('teams/{id}','LiveScoreController@GetTeam');
Route::any('seasons/{seasonId}/stats', 'LiveScoreController@GetSeasonStats');
Route::any('countries','LiveScoreController@GetCountries');
Route::any('livescoreplayers','LiveScoreController@GetPlayers');
Route::any('livescoreplayer/stats/{id}','LiveScoreController@GetPlayerStats');
Route::any('livescoreplayer/info/{id}','LiveScoreController@GetPlayerInfo');
Route::any('livescoreplayer/articles/{id}','LiveScoreController@GetPlayerArticles');
Route::any('livescoreplayer/{id}','LiveScoreController@GetPlayer');
Route::any('livescoreplayersforplayers','LiveScoreController@GetPlayersForPlayers');
Route::any('livescoreplayers/search/{s}','LiveScoreController@GetPlayersSearch');
//Route::any('standings','LiveScoreController@GetCountries');
Route::any('standings/{league}/{season?}','LiveScoreController@GetStandings');
Route::any('standings-mobile','LiveScoreController@GetStandingsMobile');

Route::any('rounds/{season}','LiveScoreController@GetRounds');

/*
 * Ads
 */

Route::get('sidebar', 'SidebarController@index');
Route::post('sidebar/update', 'SidebarController@update');


Route::get('livescorestatus', 'SidebarController@livescorestatus');
Route::post('livescorestatus/update', 'SidebarController@livescorestatus_update');


/*
 * Ads
 */

Route::get('ads', 'AdsController@index');
Route::get('promotions', 'AdsController@index');
Route::get('ads/item/{id}', 'AdsController@getItem');
Route::post('ads/delete/{id}', 'AdsController@destroy');
Route::get('ads/reset/{id}', 'AdsController@resetViewCount');
Route::post('ads/item/', 'AdsController@setItem');
Route::post('ads/item/{id}', 'AdsController@setItem');
Route::get('ads/positions', 'AdsController@getPositions');

/*
 * Clubs
 */
Route::get('clubs', 'ClubsController@index');
Route::any('clubs/search/{s}', 'ClubsController@search');

Route::get('clubs/item/{id}', 'ClubsController@getItem');
Route::post('clubs/item/', 'ClubsController@setItem');
Route::post('clubs/item/{id}', 'ClubsController@setItem');
Route::post('clubs/delete/{id}', 'ClubsController@unsetItem');

/*
 * Players
 */
Route::get('players', 'PlayerController@index');
Route::any('players/search/{s}', 'PlayerController@search');

Route::get('players/item/{id}', 'PlayerController@getItem');
Route::post('players/item/', 'PlayerController@setItem');
Route::post('players/item/{id}', 'PlayerController@setItem');
Route::post('players/delete/{id}', 'PlayerController@unsetItem');

/*
 * Leagues Relation
 */

Route::get('leaguesrel', 'LeagueController@index');

Route::get('leaguesrel/item/{id}', 'LeagueController@getItem');
Route::post('leaguesrel/item/', 'LeagueController@setItem');
Route::post('leaguesrel/item/{id}', 'LeagueController@setItem');
Route::post('leaguesrel/delete/{id}', 'LeagueController@unsetItem');


/*
 * CyberSport
 */

Route::prefix('cybersport')->group(function(){
    Route::resource('players', 'CyberSport\PlayersController', [
        'except' => ['create', 'edit']
    ]);
    Route::resource('groups', 'CyberSport\GroupsController', [
        'except' => ['create', 'edit', 'store', 'delete']
    ]);
    Route::resource('matches', 'CyberSport\MatchesController', [
        'except' => ['create', 'edit']
    ]);
    Route::post('groups/{id}/generate_matches', 'CyberSport\GroupsController@generateMatches');
    Route::post('groups/{id}/advance', 'CyberSport\GroupsController@advanceToNextStage');
});

/*
 * FA Cup
 */

Route::prefix('fa-cup')->group(function(){
    Route::resource('players', 'FACup\PlayersController', [
        'except' => ['create', 'edit']
    ]);
    Route::resource('groups', 'FACup\GroupsController', [
        'except' => ['create', 'edit', 'store', 'delete']
    ]);
    Route::resource('matches', 'FACup\MatchesController', [
        'except' => ['create', 'edit']
    ]);
    Route::post('groups/{id}/generate_matches', 'FACup\GroupsController@generateMatches');
    Route::post('groups/{id}/advance', 'FACup\GroupsController@advanceToNextStage');
});

    Route::post('articles/{id}/like', 'ArticlesController@like');
    Route::post('articles/{id}/unlike', 'ArticlesController@unlike');


Route::group(['middleware' => 'cacheable'], function(){
    /*
     * Users
     */
    Route::resource('users', 'UsersController', [
        'except' => ['create', 'edit']
    ]);

    Route::get('search/{s}','SearchController@search');
    Route::get('search','SearchController@search');
    Route::get('search-mobile','SearchController@searchMobile');

    /*
     * Tags
     */
    Route::post('tags/search', 'TagsController@search');
    Route::resource('tags', 'TagsController', [
        'except' => ['create', 'edit']
    ]);
    /*
     * Articles
     */
    Route::get('articles/slug/{slug}', 'ArticlesController@showWithSlug');
    Route::get('articles/search', 'ArticlesController@search');
    Route::get('articles/todayviews', 'ArticlesController@todayViews');
    Route::get('articles/userstats', 'ArticlesController@getStatsByUser');
    Route::get('articles/userstatsbydate', 'ArticlesController@getStatsByUserByDate');
    Route::get('articles/top', 'ArticlesController@getTopNews');
    Route::get('articles/subscribed', 'ArticlesController@getSubscribed');
    Route::get('articles/{id}/similar', 'ArticlesController@getSimilar');
    Route::resource('articles', 'ArticlesController', [
        'except' => ['create', 'edit']
    ]);
    Route::resource('article-anchors', 'AnchorsController', [
        'except' => ['create', 'edit']
    ]);
    Route::resource('prerolls', 'PrerollController', [
        'except' => ['create', 'edit']
    ]);
    Route::get('/hashtags', 'HashtagsController@index');
    Route::get('/hashtags/{hashtag}/articles', 'HashtagsController@getArticles');
    /*
     * Categories
     */
    Route::resource('categories', 'CategoriesController', [
        'except' => ['create', 'edit']
    ]);
    /*
     * Home Categories
     */
    Route::resource('home-categories', 'HomeCategoriesController', [
        'except' => ['create', 'edit']
    ]);
    /*
     * Slides
     */
    Route::resource('slides', 'SlidesController', [
        'except' => ['create', 'edit']
    ]);
    /*
     * Polls
     */
    Route::get('polls/latest', 'PollsController@latest');
    Route::resource('polls', 'PollsController', [
        'except' => ['create', 'edit']
    ]);
    /*
     * TV Programs
     */
    Route::resource('tv-programs', 'TVProgramsController', [
        'except' => ['create', 'edit', 'update']
    ]);
    /*
     * Top Teams
     */
    Route::resource('top-teams', 'TopTeamsController', [
        'except' => ['create', 'edit']
    ]);
    /*
     * Albums
     */
    Route::resource('albums', 'GalleryAlbumsController', [
        'except' => ['create', 'edit']
    ]);
    /*
     * Videos
     */
    Route::resource('videos', 'VideosController', [
        'except' => ['create', 'edit']
    ]);

    Route::get('new_videos','NewVideosController@index');

    Route::get('new_videos/item/{id}', 'NewVideosController@getItem');
    Route::post('new_videos/item/', 'NewVideosController@setItem');
    Route::post('new_videos/item/{id}', 'NewVideosController@setItem');
    Route::post('new_videos/delete/{id}', 'NewVideosController@unsetItem');
    Route::post('new_videos/upload', 'NewVideosController@UploadVideo');


    Route::get('new_polls','NewPollsController@index');

    Route::get('new_polls/item/{id}', 'NewPollsController@getItem');
    Route::post('new_polls/item', 'NewPollsController@setItem');
    Route::post('new_polls/item/{id}', 'NewPollsController@setItem');
    Route::post('new_polls/delete/{id}', 'NewPollsController@unsetItem');
    Route::post('new_polls/answer', 'NewPollsController@setAnswer');

    /*
     * Transfer Seasons
     */
    Route::resource('transfer-seasons', 'TransferSeasonController', [
        'except' => ['create', 'edit']
    ]);
    /*
     * Transfers
     */
    Route::get('transfers/leagues', 'TransferController@getLeagues');
    Route::resource('transfers', 'TransferController', [
        'except' => ['create', 'edit']
    ]);
    /*
     * Standings
     */
    Route::resource('standings', 'StandingsController', [
        'except' => ['create', 'edit']
    ]);

    /*
     * parsed
     */
    Route::get('parsed_articles','ParsedArticlesController@index');
    Route::get('get_parsed_articles/{id}','ParsedArticlesController@detail');
});


Route::get('/socialauth/{provider}', 'SocialAuthController@socialAuthByFront');
Route::get('/sign-in/apple/token', 'SocialAuthController@getVerificationToken');
Route::post('/sign-in/apple', 'SocialAuthController@signInWithApple');

Route::get('favorites/save', 'FavoriteController@saveMatch');
Route::get('favorites/remove', 'FavoriteController@removeMatch');
Route::get('favorites', 'FavoriteController@get');
Route::get('favorites/dev-id', 'FavoriteController@getDeviceId');

Route::prefix('mobile')->group(function(){
    Route::get('tab_categories/', 'Mobile\CategoriesController@tabCategories');
});

Route::get('/sub-menu', 'MainController@getSubmenuItems');

Route::get('/testt', function() {
    dd(\App\Models\Preroll::getPreroll()->name);
});
