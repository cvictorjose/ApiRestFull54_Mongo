<?php

namespace App\Http\Controllers;

use App\Notifications\Welcome;
use App\SocialUserResolver;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Server\Twitter;
use Log;


class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from a Provider.
     *
     * @return Response
     */
    public function Callback(Request $request, $provider)
    {

        $requestUri = request('redirectUri', $default = null);
        $socialiteProvider=Socialite::driver($provider);
        if($provider=='twitter'){
            if(request('oauth_verifier', $default = null)){
                $tempToken=request('oauth_token', $default = null);
                $tempIdentifier=request('oauth_verifier', $default = null);
                $config=Config::get('services.twitter');
                $config['redirect']=$requestUri;
                $srv=new Twitter($this->formatConfig($config));
                $temporaryCredentials = new TemporaryCredentials();
                $temporaryCredentials->setIdentifier($tempToken);
                $temporaryCredentials->setSecret($tempIdentifier);
                $cred=$srv->getTokenCredentials($temporaryCredentials,$tempToken,$tempIdentifier);
                $user = $socialiteProvider->userFromTokenAndSecret($cred->getIdentifier(),$cred->getSecret());
            }else{
                // @Todo:need optimization
                $config=Config::get('services.twitter');
                $config['redirect']=$requestUri;
                $srv=new Twitter($this->formatConfig($config));
                $cred=$srv->getTemporaryCredentials();
                $request = request();
                /*$request->session()->put(
                    'oauth.temp', $cred
                );*/
                return response(array("oauth_token" =>  $cred->getIdentifier()), 200);
            }
        } else
            $user = $socialiteProvider->redirectUrl($requestUri)->stateless()->user();

            $new_social=$request->input('new_social');
            if(Auth::user() && $new_social==1)
                $authUser = $this->addSocial($user, $provider);
            else
                $authUser = $this->findOrCreateUser($user, $provider);

        return $authUser;
    }


    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param  $user Socialite user object
     * @param $provider Social auth provider
     * @return  Response
     */
    public function findOrCreateUser($user, $provider)
    {

        $user_return="";
        $checkUser = User::where('email', $user->email)->first();
        $checkProvider = User::where('social.name',$provider)->where('social.id', $user->id)->first();
        if (($user->email && count($checkUser)>0 ) || $checkProvider){

            if ($checkProvider == true){
                $soc =array();
                foreach ($checkProvider->social as $social){

                    if ($social['name'] === $provider){
                        $social['token']        = $user->token;
                        if($provider!='twitter'){
                            $social['refreshToken'] = $user->refreshToken;
                            $social['expiresIn']    = $user->expiresIn;
                        }
                        $social['avatar']       = $user->avatar;
                    }
                    $soc[]=$social;
                }
                $checkProvider->social = $soc;
                $user_return= $checkProvider;
                $user_return->save();

            }else{
                //update field social
                $newprovider=new \stdClass();
                $newprovider->name=$provider;
                $newprovider->id            = $user->id;
                $newprovider->token         = $user->token;
                if($provider!='twitter'){
                    $newprovider->refreshToken  = $user->refreshToken;
                    $newprovider->expiresIn     = $user->expiresIn;
                }
                $newprovider->avatar        = $user->avatar;

                $newsocial= $checkUser->social;
                array_push($newsocial,$newprovider);
                $user_return=User::where('email', $user->email)->first();
                $user_return->update(['social' => $newsocial]);
            }

        }else{

            $prd=new \stdClass();
            $prd->name=$provider;
            $prd->id            = $user->id;
            $prd->token         = $user->token;
            if($provider!='twitter'){
                $prd->refreshToken  = $user->refreshToken;
                $prd->expiresIn     = $user->expiresIn;
            }
            $prd->avatar        = $user->avatar;


            $new_photo = new \stdClass();
            $new_photo->content = "data:image";
            $new_photo->extension = "jpg";
            $new_photo->code = base64_encode(file_get_contents($user->avatar));

            $rand = substr(md5(microtime()),rand(0,26),5);
            $user_name=isset($user->name)?str_replace (" ", "", $user->name) : "";
            $user_lastname=isset($user->last_name)? "_".str_replace (" ", "", $user->last_name) : "";
            $fullname=$user_name.$user_lastname."_".$rand;


            $username=(isset($user->nickname) && $user->nickname!='')? $user->nickname: $fullname;
            $active=($user->email && $user->nickname && $user->name)?1:0;

            //empty resource
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

            $user_return=User::create(array(
                'name'           => $user->name,
                'surname'        => $user->last_name,
                'username'       => $username,
                'email'          => $user->email,
                'email_verified' => null,
                'bio'            => null,
                'abstract'       => null,
                'cover_photo'    => null,
                'profile_photo'  => $new_photo,
                'social'         => array($prd),
                'active'         => $active,
                'password'       => bcrypt('secret'),
                'remember_token' => str_random(10),
                'lang'           => "en",
                'role'           => array('user'),
                'like_story_id'  => array(),
                'follow_user_id' => array(),
                'like_comment_id' => array(),
                'followers' => 0,
                'searches' => array(),
                //'verify_token'   => ($active==1)? null : str_random(40),
                'verify_token'   => null,
                'resources'       => $empyResource,
                    'newsletter'      => $empNewslettere,
                    'membership_active'=> array(),
                    'favourite_genres'=> array(),
                    'web_store'       => $empWebstore
            ));
        }

        $userdata = array(
            'provider' => $provider,
            'token'    => $user->token,
        );


        //Log::info('SOCIAL: '. print_r($userdata, true));
        return $this->createMessage(array(
            //"message"       =>  "Login successful",
            "user"          =>  $user_return->info(),
            "access_token"  =>  $this->getAccessTokenSocial($userdata),
            //"session_cookie"=>  (isset($_COOKIE['theland_session']))  ? $_COOKIE['theland_session'] : '',
            //"XSRF-TOKEN"    =>  (isset($_COOKIE['XSRF-TOKEN']))  ? $_COOKIE['XSRF-TOKEN'] : ''
        ), "200");
    }



    /**
     * Update array  social to user authenticated.
     *
     * @param  $user Socialite user object
     * @param $provider Social auth provider
     * @return  Response
     */
    public function addSocial($user, $provider)
    {
        $checkProvider = User::where('social.name',$provider)->where('social.id', $user->id)->first();
        //$checkProvider = User::where('social.name',$provider)->where('social.id', $user)->first();

        $newprovider        = new \stdClass();
        $newprovider->name  = $provider;
        $newprovider->id    = $user->id;
        $newprovider->token = $user->token;
        if($provider!='twitter'){
            $newprovider->refreshToken  = $user->refreshToken;
            $newprovider->expiresIn     = $user->expiresIn;
        }
        $newprovider->avatar = $user->avatar;

        $social_list= Auth::user()->social;

        if ($checkProvider == true)
        {
            foreach ($social_list as $key => $value)
            {
                if ($provider === $value['name']) {
                    array_splice($social_list, $key, 1);

                }
            }
        }
        array_push($social_list,$newprovider);
        Auth::user()->update(['social' => $social_list]);
        return $this->createMessage('UPDATED_SOCIAL_PROVIDER',"200");

    }


    public function formatConfig(array $config)
    {
        return array_merge([
            'identifier' => $config['client_id'],
            'secret' => $config['client_secret'],
            'callback_uri' => $config['redirect'],
        ], $config);
    }



}
