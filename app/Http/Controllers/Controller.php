<?php

namespace App\Http\Controllers;

use App\Jobs\GetFeedsUserSocial;
use App\Notifications\NotifyUser;
use App\User;
use App\Book;
use App\Genre;
use App\Story;
use Exception;
use GuzzleHttp;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Dawson\AmazonECS\AmazonECSFacade as Amazon;
use Thujohn\Twitter\Facades\Twitter;
use Feeds;
use Facebook\Facebook;



class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $method;
    //public $instance = false;
    public $lang;


    public function __construct (Request $request){
        $this->checkLang($request->header('lang'));
        $this->method = $request->method();
        $this->lang = App::getLocale();
    }

    /**
     * Set client Language
     *
     * @param  string  $lang
     * @return \Illuminate\Http\Response
     */
    public function checkLang($lang){
        $langs=['en','it'];
        if (in_array($lang,$langs))App::setLocale($lang);
    }

    /**
     * Return Label messages - 200 status code
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createMessage($msg, $code)
    {
        return response()->json(['data' => $msg, 'code' => $code], $code);
    }


    /**
     * Follow User/Board/Place.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    protected $obj_followed = false;

    public function setFollow(Request $request, $model, $follow_id)
    {
        try {
            $user_id=Input::get('user._id');
            $user=User::find($user_id);
            if(count($user)){

                $NamespacedModel = '\\App\\' . ucfirst($model);
                $obj_followed= $NamespacedModel::find($follow_id);


                switch($this->method)
                {
                    case 'POST':
                    {
                        if ($user->checkArrayId($follow_id, $model) == false){
                            $user->setFollowers($follow_id, $model,"follow");
                            $obj_followed->setTotalFollowers("+");
                            $obj_followed->notify(new NotifyUser($user, false,'followUser','',false));


                            $user_all = User::all();
                            foreach ($user_all as $user_friend){
                                if ($user_friend->checkArrayId($user_id, 'user') == true){
                                    $user_friend->notify(new NotifyUser($obj_followed, false,'yourFollowers',$user_id,false));
                                }
                            }

                        }else{
                            return $this->createMessage($obj_followed,"200");
                        }

                    }
                        break;

                    case 'DELETE':
                    {
                        if ($user->checkArrayId($follow_id, $model) == true){
                            $user->setFollowers($follow_id, $model,"unfollow");
                            $obj_followed->setTotalFollowers("-");
                        }else{
                            return $this->createMessage($obj_followed,"200");
                        }
                    }
                        break;

                    default: break;
                }

            }else{
                abort(404, 'NOT_FOUND');
            }

            return $this->createMessage($obj_followed,"200");

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }



    /**
     * Add a story_id to field Like_story_id in user collection.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    protected $obj_liked = false;

    public function setLike(Request $request, $model, $like_id)
    {
        try {
            $user_id=Input::get('user._id');
            $user=User::find($user_id);
            if(count($user)){

                $NamespacedModel = '\\App\\' . ucfirst($model);

                switch($model)
                {
                    case 'story':
                    {
                        $obj_liked= $NamespacedModel::find($like_id);
                        $owner=$obj_liked->user()->first();
                        $type="likeStory";
                    }
                        break;

                    case 'comment':
                    {
                        $story= Story::where('comments._id',$like_id)->first();
                        $obj_liked = $story->comments()->where('_id', $like_id)->first();
                        $owner=$obj_liked->user()->first();
                        $type="likeComment";

                    }
                        break;

                    default: abort(404, 'NOT_FOUND');
                }



                switch($this->method)
                {
                    case 'POST':
                    {

                        if ($user->checkArrayId($like_id, $model) == false){
                            $user->setLikes($like_id, $model,"like");
                            $obj_liked->setTotalLikes("+");

                            if ($user_id !=$owner->_id)

                                if($model==="comment"){
                                    $owner->notify(new NotifyUser($user, false, $type,$story,false));
                                }else{
                                    $owner->notify(new NotifyUser($user, false, $type,$obj_liked,false));
                                }



                        }else{
                            return $this->createMessage($obj_liked,"200");
                        }

                    }
                        break;

                    case 'DELETE':
                    {
                        if ($user->checkArrayId($like_id, $model) == true){
                            $user->setLikes($like_id, $model,"unlike");
                            $obj_liked->setTotalLikes("-");
                        }else{
                            return $this->createMessage($obj_liked,"200");
                        }
                    }
                        break;

                    default: abort(404, 'NOT_FOUND');
                }

            }else{
                abort(404, 'NOT_FOUND');
            }

            return $this->createMessage($obj_liked,"200");

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Base64 Photo.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function getValuePhoto($imageData){

        try {
            // $data = 'data:image/jpeg;base64,/9';
            list($type, $imageData) = explode(';', $imageData);
            list(,$extension)       = explode('/',$type);
            list(,$content)         = explode('data:',$type);
            list(,$imageData)       = explode(',', $imageData);

            $new_photo = new \stdClass();
            $new_photo->content   = $content;
            $new_photo->extension = $extension;
            $new_photo->code      = $imageData;

            return $new_photo;

        } catch (Exception $e) {
            \Log::info('Error convertImgBase64: '.$e);
            $this->insertErrorDebug('Error convertImgBase64: '.$e);
            return $this->createMessageError($e->getMessage(),$e->getStatusCode());
        }
    }



    /**
     * Return an Token to access the API.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAccessTokenSocial($userdata)
    {
        $http = new GuzzleHttp\Client(['verify' => false]);
        $response = $http->post(env('APP_URL').'/oauth/token',  [
            'form_params' => [
                'grant_type'    => 'social',
                'client_id'     => env('PP_CID'),
                'client_secret' => env('PP_CSECRET'),
                'network'       => $userdata['provider'], /// or any other network that your server is able to resolve.
                'access_token'  => $userdata['token']
            ]
        ]);
        $accessToken = json_decode((string) $response->getBody(), true)['access_token'];
        return $accessToken;
    }



    /**
     * Return an Token to access the API.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAccessToken($userdata)
    {
       // $http = new GuzzleHttp\Client;
        $http = new GuzzleHttp\Client([
            'verify' => false
        ]);

        $response = $http->post(env('APP_URL').'/oauth/token',  [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => env('PP_CID'),
                'client_secret' => env('PP_CSECRET'),
                'username' => $userdata['email'],
                'password' => $userdata['password'],
                'scope' => '']
        ]);
        $accessToken = json_decode((string) $response->getBody(), true)['access_token'];
        return $accessToken;
    }


    //Catch error custom
    public function createCodeMessageError($e)
    {

        switch($e->getMessage())
        {
            case 'BAD_REQUEST':
            {
                return response()->json(['error' => trans('error.BAD_REQUEST'), 'code' => 400], 400);
            }
                break;

            case 'UNAUTHORIZED':
            {
                return response()->json(['error' => trans('error.UNAUTHORIZED'), 'code' => 401], 401);
            }
                break;

            case 'FORBIDDEN':
            {
                return response()->json(['error' => trans('error.FORBIDDEN'), 'code' => 403], 403);
            }
                break;

            case 'NOT_FOUND':
            {
                return response()->json(['error' => trans('error.NOT_FOUND'), 'code' => 404], 404);
            }
                break;

            case 'INSTANCE_IS_REQUIRED':
            {
                return response()->json(['error' => trans('error.INSTANCE_IS_REQUIRED'), 'code' => 400], 400);
            }
                break;

            case 'PARAMETER_INVALID':
            {
                return response()->json(['error' => trans('error.PARAMETER_INVALID'), 'code' => 400], 400);
            }
                break;

            case 'PARAMETER_REQUIRED':
            {
                return response()->json(['error' => trans('error.PARAMETER_REQUIRED'), 'code' => 400], 400);
            }
                break;


            default :
            {
                //\Log::info('UPDATE Comment: '.$e);
                $this->insertErrorDebug('ERROR: '.$e->getMessage() .' in '. $e->getFile() .' Line '.$e->getLine());
                return $this->createMessageException(array($e->getMessage(), $e->getFile() . ' Line ' . $e->getLine()), "INTERNAL_SERVER_ERROR", "500");
            }
        }
    }

    /**
     * Create log file, record the error
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function insertErrorDebug ($msg){
        $log = new Logger('name');
        $log->pushHandler(new RotatingFileHandler(storage_path().'/logs/debug/debug.log',2,Logger::INFO));
        $log->info($msg);
    }


    /**
     * Return Error Label + StatusCode
     *
     * @param  string  $msg
     * @return \Illuminate\Http\Response
     */
    public function createMessageError($msg, $code)
    {
        return response()->json(['error' => $msg, 'code' => $code], $code);
    }


    /**
     * Return all erros not in list in lang/errors.php
     *
     * @param  string  $msg
     * @return \Illuminate\Http\Response
     */
    public function createMessageException($msg, $error, $code)
    {
        return response()->json([ 'message'=>$msg, 'error' => $error,'code' => $code], $code);
    }

    /**
     * Update all users feeds social
     *
     * @param  string  $msg
     * @return \Illuminate\Http\Response
     */
    public function updateFeeds()
    {
        $social_list=['facebook','twitter','rss'];
        foreach ($social_list as $social)
        {
           /* DB::table('story')->delete();*/
            dispatch(new GetFeedsUserSocial($social));
        }
    }


    /**
     * Get Photo_id into Body and move the image from preload/* to media/*
     *
     * @param  string  $msg
     * @return \Illuminate\Http\Response
     */

    public function moveFileToMedia($id)
    {
        try {
            $story = Story::find($id);
            //https://vow.sciamlab.com/api_test/media/images
            //preg_match_all("/https:\/\/vow.sciamlab.com\/api_test\/media\/images\/([^(\s|!\?)]+)/", $story->body, $result);

            preg_match_all("/media\/images\/([^(\s|!\")]+)/", $story->body, $result);

            foreach ($result[1] as $file)
            {
                $folders=["preload_img"];
                foreach ($folders as $f)
                {
                    $get_file= Storage::disk($f)->exists($file);
                    if($get_file){
                        //var_dump($get_file);
                        switch($f)
                        {
                            case 'preload_img':
                            {
                              Storage::disk('media')->move('preload/images/'.$file, 'images/'.$file);
                            }
                            break;

                            default: abort(404, 'NOT_FOUND');
                        }
                    }

                }
            }
            //return "moved";
        } catch (Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    public function geoNameCountry(){
        $http = new GuzzleHttp\Client();
        return response($http->get('http://api.geonames.org/countryInfoJSON?username='.env('GEONAMES_USERNAME'))->getBody())->header('Content-Type', 'application/json');
    }

    public function geoNameChildren(){
        $id=Input::get('geonameId');
        $http = new GuzzleHttp\Client();
        return response($http->get('http://api.geonames.org/childrenJSON?username='.env('GEONAMES_USERNAME').'&geonameId='.$id)->getBody())->header('Content-Type', 'application/json');
    }


    public function amazonTest(){
        $product = Amazon::lookup('cazzo')->json();
        print_r($product['Items']['Request']['Errors']);
        return $product;
    }

    public function tweetTest(){
        return Twitter::getUserTimeline(['screen_name' => 'pastarace','count' => 100, 'format' => 'json']);
    }

    public function facebookTest(){
        $config=Config::get('services.facebook');

        $fb = new Facebook([
            'app_id' => $config['client_id'],
            'app_secret' => $config['client_secret'],
            'default_graph_version' => 'v2.9',
            'default_access_token' => $config['client_id'].'|'.$config['client_secret'],
        ]);

        $request = $fb->request(
            'GET', '/',array ('id' => 'https://www.facebook.com/PiuminiDanesi/')
        );

        $page = $fb->getClient()->sendRequest($request)->getGraphPage();
        $response=$fb->get('/'.$page->getId().'/posts?fields=caption,description,message,story,link,properties,feed_targeting,source,type,status_type,attachments{media,type,url,title},name');
        return json_encode($response->getDecodedBody());

    }

    public function feedTest(){
        $feed = Feeds::make('http://www.repubblica.it/rss/homepage/rss2.0.xml');
        $max = $feed->get_item_quantity();
        foreach ($feed->get_items(0,$max) as $item)
        {

            $new_rss= new \stdClass();
            $new_rss->id          = $item->get_id();
            $new_rss->title       = $item->get_title();
            $new_rss->description = $item->get_description();
            $new_rss->date        = $item->get_date();
            echo json_encode($new_rss);
        }

    }

    public function getMetadata(Request $request)
    {
        $url  = Input::get('link');
        $http = new GuzzleHttp\Client(['verify' => false]);
        $response = $http->get($url);

        try {
            $url  = Input::get('link');
            $sites_html = $response->getBody();
            $html = new \DOMDocument();
            @$html->loadHTML($sites_html);

            $img = "#";
            $title=""; $description=""; $blocco_img="";

            foreach($html->getElementsByTagName('meta') as $meta) {
                if($meta->getAttribute('name') =='twitter:title' || $meta->getAttribute('name') =='title' || $meta->getAttribute('property')=='og:title'){
                    $title = htmlentities($meta->getAttribute('content'));
                }
                if($meta->getAttribute('name') =='twitter:description' || $meta->getAttribute('name') =='description' || $meta->getAttribute('property') =='og:description'){
                    $description = $meta->getAttribute('content');
                }
                if($meta->getAttribute('name') =='twitter:image' || $meta->getAttribute('property') =='og:image'){
                    $img = $meta->getAttribute('content');
                    $blocco_img='<img class="card-img-top" src="'.$img.'" alt="'.$title.'">';
                }
            }

            $link= '<div class="card" style="width: 20rem;">'.$blocco_img.'<div class="card-block"><h4 class="card-title">'.$title.'</h4><p class="card-text">'.$description.'</p><a href="'.$url.'" class="btn btn-primary">details</a></div></div>';


            return $this->createMessage($link,"200");
            //return json_encode(get_meta_tags('http://nvie.com/posts/a-successful-git-branching-model'));
        } catch (Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

}
