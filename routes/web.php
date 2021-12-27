<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function(){
	// $articles = \App\Article::where('id','>',0)->orderBy('id','desc')->limit(10)->get();
	// dd($articles);


//	$content = 'asdasd asdasdasd [type="video" id="2" asdasd="Asd"] asdasdasd asdasd [type="video" id="1" asdasd="Asd"] asdasda sdasd a sd as da  sd';
//	$content = \App\Models\ShortCode::ContentShortcode($content);
//	print_r($content);
    // $title = 'Вот, что пишет авторитетное издание BBC об очередной великолепной игре Андрея Ярмоленко в АПЛ';
    // $title = 'კოუტინიო ტოტენჰემში? გადაწყვეტილება მიღებულია';
    // echo url_slug($title,['transliterate' => true]);
    // echo "<br>";
//	echo LIVESCORE_CONNECTION;
//	echo "<br>";
//	echo livescoreConnection();
//	echo "<br>";


	/*

FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
});
		
{
    status: 'connected',
    authResponse: {
        accessToken: '...',
        expiresIn:'...',
        signedRequest:'...',
        userID:'...'
    }
}
	*/

	$html = "
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '636573253770771',
      cookie     : true,
      xfbml      : true,
      version    : 'v6.0'
    });
      
    FB.AppEvents.logPageView();   
      
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = \"https://connect.facebook.net/en_US/sdk.js\";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));

   function checkLoginState() {
  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });
}
</script>


<fb:login-button 
  scope=\"public_profile,email\"
  onlogin=\"checkLoginState();\">
</fb:login-button>

<br />
                        <p style=\"margin-left:265px\">OR</p>
                        <br />
                        <div class=\"form-group\">
                            <div class=\"col-md-8 col-md-offset-4\">
                              <a href=\"".url('/redirect')."\" class=\"btn btn-primary\">Login with Facebook</a>
                            </div>
                        </div>

";

    return $html;
});

Route::any("/test",'MainController@signInWithApple');
Route::get('/ads', 'MainController@videoPlayerXml');
Route::get('/news-video/vast.xml', 'MainController@generateXml');
Route::get('/news-video/{video?}', 'MainController@videoPlayer');

//Route::get('/redirect', 'SocialAuthFacebookController@redirect');
//Route::get('/callback', 'SocialAuthFacebookController@callback');

// Route::get('/redirect/{provider}', 'SocialAuthController@redirect');
// Route::get('/callback/{provider}', 'SocialAuthController@callback');


