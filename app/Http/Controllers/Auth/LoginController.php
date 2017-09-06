<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;
use Response;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Login user from system.
     * Create Token passport
     * @return \Illuminate\Http\Response
     */
    public function doLogin(Request $request)
    {
        $login = $request->input('login');
        $login_type = filter_var( $login, FILTER_VALIDATE_EMAIL ) ? 'email' : 'username';

        $request->merge([ $login_type => $login ]);

        if ( $login_type == 'email' ) {
            $rules = array(
                'email'    => 'required|email', // make sure the email is an actual email
                'password' => 'required|alphaNum|min:6' // password can only be alphanumeric and has to be greater than 5
                // characters
            );
            $credentials = $request->only( 'email', 'password' );

        } else {
            $rules = array(
                'username' => 'required|exists:users',
                'password' => 'required|alphaNum|min:6'
            );
            $credentials = $request->only( 'username', 'password' );
        }

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            // @todo: send detailed error
            return $this->createMessageError('BAD_PARAMETERS',"400");
        } else {

           if (Auth::guard('web')->attempt($credentials)) {

                $userdata = array(
                    'email'     => Auth::guard('web')->user()->email,
                    'password'  => Input::get('password')
                );

                return $this->createMessage(array(
                    "user"          =>  Auth::guard('web')->user()->info(),
                    "access_token"  =>  $this->getAccessToken($userdata),
                    //"session_cookie"=>  (isset($_COOKIE['theland_session']))  ? $_COOKIE['theland_session'] : '',
                    //"XSRF-TOKEN"    =>  (isset($_COOKIE['XSRF-TOKEN']))  ? $_COOKIE['XSRF-TOKEN'] : ''
                ),
                    "200");
            } else {
                return $this->createMessageError('LOGIN_ERROR',"401");
            }
        }
    }

    /**
     * Display user status.
     *
     * @return \Illuminate\Http\Response
     */
    public function loginStatus()
    {

        if (Auth::user() && Auth::check()) {
            return $this->createMessage(array(
                "message" =>  'LOGIN_IN',
                "user" =>  Auth::user()
            ),
                "200");
        }else{
            return $this->createMessage(array(
                "message" =>  'NOT_LOGIN_IN'),
                "200");
        }
    }

    /**
     * Logout user from system.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::guard('web')->logout();
        return $this->createMessage('NOT_LOGIN_IN',"200");
    }


}
