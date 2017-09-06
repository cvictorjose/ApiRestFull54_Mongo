<?php

namespace App\Http\Controllers;

use App\Mail\emailUser;
use App\Membership;

use App\Notifications\NotifyUser;
use App\Story;
use App\User;
use Carbon\Carbon;
use Exception;
use Facebook\Facebook;
use Feeds;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Newsletter;

use Thujohn\Twitter\Facades\Twitter;


class UserController extends Controller
{

    public function __construct(Request $request){
        parent::__construct($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $users = User::all();
            return $this->createMessage($users,"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //DB::table('users')->delete();

        $parameters = $request->all();
        $user_validated=User::validatorUser($parameters,$this->method);

        try {

            if ($user_validated !="validated") {
               return $this->createMessageError($user_validated,"404");
            }else{
                $new_photo = empty(Input::get('profile_photo'))  ? null : $this->getValuePhoto(Input::get('profile_photo'));
                $new_cover_photo = empty(Input::get('cover_photo'))  ? null : $this->getValuePhoto(Input::get('cover_photo'));

                $empyResource=new \stdClass();
                $res=new \stdClass();
                $res->page=null;
                $res->public=false;
                $empyResource->facebook=$res;
                $empyResource->twitter=$res;
                $empyResource->rss=$res;

                $empNewslettere=new \stdClass();
                $empNewslettere->vow=false;
                $empNewslettere->writing_opportunities=false;

                $empWebstore=new \stdClass();
                $empWebstore->url=null;
                $empWebstore->verified=false;


                $user=User::create(array(
                    'name'            => $parameters['name'],
                    'surname'         => $parameters['surname'],
                    'username'        => $parameters['username'],
                    'email'           => $parameters['email'],
                    'email_verified'  => $parameters['email'],
                    'bio'             => empty($parameters['bio']) ? null : (string) $parameters['bio'],
                    'abstract'        => empty($parameters['abstract']) ? null : (string) $parameters['abstract'],
                    'cover_photo'     => $new_cover_photo,
                    'social'          => array(),
                    'active'          => 0,
                    'password'        => bcrypt($parameters['password']),
                    'remember_token'  => str_random(10),
                    'profile_photo'   => $new_photo,
                    'lang'            => empty($this->lang)? "en" : (string) $this->lang,
                    'like_story_id'   => array(),
                    'like_comment_id' => array(),
                    'follow_user_id'  => array(),
                    'followers'       => 0,
                    'searches'        => array(),
                    'role'            => array('user'),
                    'verify_token'    => str_random(40),
                    'resources'       => $empyResource,
                    'newsletter'      => $empNewslettere,
                    'membership_active'=> array(),
                    'favourite_genres'=> array(),
                    'web_store'       => $empWebstore

                ));

                $user->notify(new NotifyUser($user, env('APP_FRONT_URL'), 'welcome'));
                $userdata = array('email' => $user->email,'password'  => $parameters['password']);

                return $this->createMessage(array(
                    "user"          =>  $user->info(),
                    "access_token"  =>  $this->getAccessToken($userdata),
                ),"200");
            }

        }
        catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $user= User::where('_id', $id)->first();
            if ($user){
                $pattern = '/(FacebookExternalHit|GoogleBot|Facebot|Twitterbot)/i';
                $agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_ENCODED);
                if(preg_match($pattern,$agent)){
                    return View('social.user')->with(array('user'=> $user, 'host'=>$request->getHost()));
                }else{

                    if(Auth::user() && Auth::user()->isAdmin())
                        $return=$user->info();
                    else
                        $return=$user->infoSmall();
                    return $this->createMessage($return,"200");
                }
            }
            abort(404, 'NOT_FOUND');

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $sendNotifyAdmin=0;
            $sendNotifyWS=0;
            $changeWs=0;

            $user = User::find($id);

            $input = $request->only('name','surname','email','username', 'password',
                'profile_photo','cover_photo', 'bio','abstract',
                'lang','like_story_id','follow_user_id', 'role','like_comment_id','resources',
                'newsletter', 'last_feed','favourite_genres','web_store','country','state','province');


            $check_data=array('active'=>'1', 'id'=>$id);
            foreach($input as $column => $value)
            {
                switch($column)
                {
                    case 'username':
                        if ($user->username != $value){
                            if (User::checkUsername($value)) return $this->createMessageError('USERNAME_BUSY',"400");
                        }
                        $check_data[$column]= $value;
                        break;

                    case 'email':
                        if ($user->email != $value){
                            if (User::checkEmail($value)) return $this->createMessageError('EMAIL_BUSY',"400");
                        }
                        $check_data[$column]= $value;
                        break;

                    case 'password':
                        $check_data[$column] = empty($value) ? 'noupdate' : $value;
                        break;

                    default:
                        $check_data[$column]= $value;

                }
            }



            $user_validated=User::validatorUser($check_data,$this->method);
            if ($user_validated !="validated") {
                return $this->createMessageError($user_validated,"400");
            }else{


                foreach($input as $column => $value)
                {
                    if($value!=null)
                        switch ($column){
                            case "profile_photo":
                                $user->profile_photo = ($value && $value!='') ? $this->getValuePhoto($value) : $user->profile_photo;
                                break;
                            case "cover_photo":
                                $user->cover_photo = ($value && $value!='') ? $this->getValuePhoto($value) : $user->cover_photo;
                                break;
                            case "password":
                                if($value!='')
                                    $user->password=bcrypt($value);
                                break;

                            case "email":
                                if($user->email != $value)
                                    $user->email_verified=$value;
                                break;

                            case "role":
                                break;

                            case "searches":
                                break;

                            case "like_story_id":
                                $user->{$column} = empty($value) ?  array() : $value;
                                break;

                            case "like_comment_id":
                                $user->{$column} = empty($value) ?  array() : $value;
                                break;


                            case "follow_user_id":
                                $user->{$column} = empty($value) ?  array() : $value;
                                break;

                            case "social":
                                $user->{$column} = empty($value) ?  array() : $value;
                                break;

                            case "web_store":

                               $user_ws= (object)$user->web_store;

                                if($user_ws->url != $value['url']){
                                    $new_ws           = $user_ws;
                                    $new_ws->url      = $value['url'];
                                    $new_ws->verified = 0;
                                    $user->web_store = $new_ws;
                                    $sendNotifyAdmin=1;
                                    $changeWs=1;
                                }


                                if($value['verified'] > 0 && $changeWs < 1){
                                    $new_ws           = $user_ws;
                                    $new_ws->url      = $value['url'];
                                    $new_ws->verified = 1;
                                    $user->web_store = $new_ws;
                                    $sendNotifyWS=1;
                                }

                                break;

                            default:
                                $user->{$column}=$value;
                                break;
                        }
                }

                $user->setActive();
                $user->save();

                if($user->email_verified != null){
                   $user->notify(new NotifyUser($user,env('APP_FRONT_URL'),'changeEmail'));
                }


                if($sendNotifyAdmin){
                    $admins=User::where('role','all',['admin'])->get();
                    foreach($admins as $ad){
                        $ad->notify(new NotifyUser($user,false,'changeWebStore', false, true));
                    }
                }


                if($sendNotifyWS){
                    $user->notify(new NotifyUser($user,false,'confirmWebStore', false, true));
                }

                if($user->newsletter['vow'])
                    Newsletter::subscribe($user->email, ['FNAME'=> $user->name, 'LNAME'=> $user->surname], 'vow');
                else
                    Newsletter::unsubscribe($user->email, 'vow');
                if($user->newsletter['writing_opportunities'])
                    Newsletter::subscribe($user->email, ['FNAME'=> $user->name, 'LNAME'=> $user->surname], 'writing_opportunities');
                else
                    Newsletter::unsubscribe($user->email, 'writing_opportunities');

                //return $this->createMessage($user,"200");
                return $this->createMessage($user->info(),"200");
            }

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $result = User::where('_id', $id)->firstOrFail();
            Story::where('user_id',$result->_id)->delete();
            /*Newsletter::unsubscribe($result->email, 'writing_opportunities');
            Newsletter::unsubscribe($result->email, 'vow');*/
            $result->delete();

            return $this->createMessage($result,"200");
        } catch (Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Check email user and return true or false.
     *
     * @param  varchar  $email
     * @return \Illuminate\Http\Response
     */
    public function checkEmail($email)
    {
        try {

            if (isset(Auth::user()->email) &&  Auth::user()->email === $email)
                return $this->createMessage("false","200");

            $result=User::checkEmail($email);
            return $this->createMessage($result,"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Check username user and return true or false.
     *
     * @param  string  $username
     * @return \Illuminate\Http\Response
     */
    public function checkUsername($username)
    {
        try {
            if (isset(Auth::user()->username) &&  Auth::user()->username === $username)
                return $this->createMessage("false","200");

            $result=User::checkUsername($username);
            return $this->createMessage($result,"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Check valid resource.
     *
     * @param  string  $type
     * @return \Illuminate\Http\Response
     */
    public function checkResource($type,Request $request)
    {
        $result=false;
        try {
            $id=Input::get('id');
            if(!$id && !$type){
                throw new Exception('Invalid request');
            }
            switch ($type) {
                case 'facebook':
                    $config=Config::get('services.facebook');

                    $fb = new Facebook([
                        'app_id' => $config['client_id'],
                        'app_secret' => $config['client_secret'],
                        'default_graph_version' => 'v2.9',
                        'default_access_token' => $config['client_id'].'|'.$config['client_secret'],
                    ]);
                    $request = $fb->request('GET', '/', array ('id' => $id));
                    $fbId = $fb->getClient()->sendRequest($request)->getGraphPage()->getid();
                    if($fbId!=$id)
                        $result=true;
                    else
                        $result=false;

                    break;
                case 'rss':
                    $feed = Feeds::make($id);
                    if ($feed->error())
                        $result=false;
                    else
                        $result=true;
                    break;
                case 'twitter':
                    $id=str_replace('@','',$id);
                    $user=Twitter::getUsers(['screen_name'=>$id]);
                    if($user)
                        $result=true;
                    break;
                default:
                    $result=false;
            }
            return $this->createMessage($result,"200");
        } catch (\Exception $e) {
            return $this->createMessage($result,"200");
           /* return $this->createCodeMessageError($e);*/
        }
    }



    /**
     * Get list of User For autocomplete.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function autocomplete()
    {
        try {
            $string=Input::get('s');
            if($string!='') {
                $result = User::select(['_id', 'username'])->where('username', 'like', $string . '%')->get();
            }else
                $result=array();
            return $this->createMessage($result,"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Verification Token and Update active field as 1.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyEmail(Request $request)
    {
        try {
            $token=$request->only('token');
            $user= User::where('verify_token',$token['token'])->first();

           if($user){
               $oldMail=$user->email;
               $user->email=$user->email_verified;
               $user->email_verified=null;
               $user->verify_token=null;
               $user->setActive();
               $user->save();
               Newsletter::unsubscribe($oldMail, 'vow');
               Newsletter::unsubscribe($oldMail, 'writing_opportunities');
               if($user->newsletter['vow'])
                   Newsletter::subscribe($user->email, ['FNAME'=> $user->name, 'LNAME'=> $user->surname], 'vow');
               if($user->newsletter['writing_opportunities'])
                   Newsletter::subscribe($user->email, ['FNAME'=> $user->name, 'LNAME'=> $user->surname], 'writing_opportunities');

               return $this->createMessage($user->info(),"200");
           }else{
               return $this->createMessageError('BAD_REQUEST',"400");
           }

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }




    public function sendConfirmEmail(Request $request)
    {
       //dd($request['email']);
        try {
            $user= User::where('email',$request['email'])->first();

            if($user){
                if($user->verify_token!=null) {
                    $user->notify(new NotifyUser($user,env('APP_FRONT_URL'),'confirmEmail'));

                   return $this->createMessage("EMAIL_SENT", "200");
                }else{
                    return $this->createMessageError('USER_ACTIVATED',"200");
                }
            }else{
                return $this->createMessageError('BAD_REQUEST',"400");
            }

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Get User's wall
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function wall(Request $request)
    {
        try {
            $stories=new \stdClass();
            $page=1;
            $size=50;
            if(Input::get('size'))
                $size=Input::get('size');
            if(Input::get('page'))
                $page=Input::get('page');
            /*
             * @todo: define a correct query for retrive related story
             *
            $me=Auth::user();
            $userLiked=Story::whereIn('_id',$me->like_story_id)->get()->pluck('_id');

            $stories->books = Story::where('type','book')->whereIn('user_id',$userLiked->merge($me->follow_user_id))->orderBy('created_at','desc')->get();
            $stories->posts = Story::where('type','post')->whereIn('user_id',$userLiked->merge($me->follow_user_id))->orderBy('created_at','desc')->get();

            $stories->books = Story::where('type','book')->whereNotNull('featured_position')->orderBy('created_at','desc')->get();
            $stories->posts = Story::where('type','post')->whereNotNull('featured_position')->orderBy('created_at','desc')->get();
            **/

            $skip=$size*($page-1);
            $me=Auth::user()->first();
            $stories=Story::whereIn('user_id',$me->follow_user_id)->orderBy('created_at','desc')->skip($skip)->take($size)->get();


            if(count($stories)){
                foreach ($stories as $s)
                    $s= $s->info();
            }

            return $this->createMessage($stories,"200");


        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Enable an User again - clear Softdelete
     *
     *
     * @return \Illuminate\Http\Response
     */

    public function enableUser($id)
    {
        try {
            $user = User::withTrashed()->find($id);
            $user->restore();
            Story::where('user_id',$user->_id)->restore();
            return $this->createMessage($user,"200");
        }
        catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }
}