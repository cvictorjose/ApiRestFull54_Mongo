<?php

header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: content-type, vow_session");

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

Auth::routes();


//SOCIAL
Route::get('redirect/{provider}', array('uses' => 'SocialAuthController@redirect'));
Route::post('auth/{provider}',    array('uses' => 'SocialAuthController@Callback'));

//OAUTH
Route::post('oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');

//LOGIN
Route::get('/', 'Auth\LoginController@loginStatus');
Route::post('login', array('uses' => 'Auth\LoginController@doLogin'));
Route::get('login',  array('uses' => 'Auth\LoginController@loginStatus'));
Route::get('logout', array('uses' => 'Auth\LoginController@logout'));

//IMG
Route::get('story/{id}/img',  array('uses' => 'ImgController@coverStory'));
Route::get('user/{id}/img',   array('uses' => 'ImgController@profileUser'));
Route::get('user/{id}/cover', array('uses' => 'ImgController@coverUser'));

//WALL
Route::post('user/{id}/wall',  array('uses' => 'UserController@wall'));
Route::get('user/wall',        array('uses' => 'UserController@wall'));

//USER
Route::post('register', array('uses' => 'UserController@store'));
Route::name('verifyEmail')->post('user/verify', 'UserController@verifyEmail');

Route::get('user/autocomplete', 'UserController@autocomplete');
Route::get('checkemail/{email}', 'UserController@checkEmail');
Route::get('checkusername/{username}', 'UserController@checkUsername');
Route::post('checkresource/{type}', 'UserController@checkResource');
Route::get('lookupamazon/{id}', 'BookController@amazonLookup');

Route::post('user/{id}/search',   array('uses' => 'SaveSearchController@store'));
Route::delete('user/{id}/search', array('uses' => 'SaveSearchController@destroy'));
Route::post('user/getusertoken',  array('uses' => 'UserController@getUserFromResetToken'));

Route::post('user/email',      array('uses' => 'UserController@sendConfirmEmail'));
Route::put('user/{id}/enable', array('uses' => 'UserController@enableUser'));


Route::resource('user', 'UserController',
    ['only' => ['index', 'store', 'update', 'destroy', 'show']]);

//COMMENT
Route::get('story/comments', 'CommentController@index');
Route::get('story/{id}/comments', 'CommentController@show');

Route::post('story/{id}/comment', 'CommentController@store');
Route::post('story/{id}/comment/{id2}', 'CommentController@store');

Route::put('story/{id}/comment/{id2}', 'CommentController@update');
Route::delete('story/{id}/comment/{id2}', 'CommentController@destroy');

//AUTH:API
Route::group(['middleware' => 'auth:api'], function () {
    //LIKE
    Route::post('{action}/{id}/like', 'Controller@setLike');
    Route::delete('{action}/{id}/like', 'Controller@setLike');

    //FOLLOW
    Route::post('{action}/{id}/follow', 'Controller@setFollow');
    Route::delete('{action}/{id}/follow', 'Controller@setFollow');

    //NOTIFICATION
    Route::get('user/{id}/notification', 'NotificationController@show');
    Route::put('user/{id}/notification', 'NotificationController@markAsRead');
});

//RELATED
Route::get('story/{id}/related', array('uses' => 'StoryController@related'));
Route::post('story/{id}/promote',array('uses' => 'StoryController@promote'));

//SEARCH
Route::get( 'story/home',       array('uses' => 'StoryController@home'));
Route::post('story/search',     array('uses' => 'StoryController@search'));
Route::post('genre/search',     array('uses' => 'GenreController@search'));
Route::get( 'tag/autocomplete', array('uses' => 'StoryController@autocompleteTags'));


//Route::group(['middleware' => 'auth:api','except' => 'show','index'], function () {
    //GENRE
    Route::resource('genre', 'GenreController',
        ['only' => ['index', 'store', 'update', 'destroy', 'show']]);

    //MEMBERSHIP
    Route::resource('membership', 'MembershipController',
        ['only' => ['index', 'store', 'update', 'destroy', 'show']]);

    //STORY
    Route::resource('story', 'StoryController',
        ['only' => ['index', 'store', 'update', 'destroy', 'show']]);
//});

//MEMBERSHIP
Route::post('user/{id}/membership/request', 'MembershipController@requestMembership');
Route::delete('user/{id}/membership/request', 'MembershipController@deleteMembership');
Route::post('user/{id}/membership/activate', 'MembershipController@activateMembership');

//JOB UPDATE FEEDS
Route::put('feeds', array('uses' => 'Controller@updateFeeds'));

//FROALA UPLOAD/GET
Route::post('media/{action}',     array('uses' => 'ImgController@UpFile'));
Route::get('media/{action}/{id}', array('uses' => 'ImgController@getShareFile'));


//METADATA
Route::post('embedexternalurl',     array('uses' => 'Controller@getMetadata'));



//PHP ARTISAN
Route::put('/seed_all', function(){
    //$exitCode =  Artisan::call('db:seed');
    Artisan::call('db:seed', ['--class'   => 'DatabaseSeeder']);
    dd(Artisan::output());
});

//GEONAME PARSER
Route::get('geonames/countryInfoJSON', 'Controller@geoNameCountry');
Route::get('geonames/childrenJSON', 'Controller@geoNameChildren');
Route::get('move/{id}', array('uses' => 'Controller@moveFileToMedia'));
/*Route::get('addsocial/{id}/{action}', array('uses' => 'SocialAuthController@addSocial'));*/
/*Route::get('move/{id}', array('uses' => 'Controller@moveFileToMedia'));*/


//test resources
Route::get('tweetTest', 'Controller@tweetTest');
Route::get('facebookTest', 'Controller@facebookTest');
Route::get('feedTest', 'Controller@feedTest');
Route::get('mediaTest', 'Controller@testMeta');