<?php

namespace App;


use App\Notifications\NotifyStory;
use App\Notifications\NotifyUser;
use App\Notifications\ResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Moloquent\Eloquent\Model as Eloquent;
use Moloquent\Eloquent\SoftDeletes;


class User extends Eloquent implements Authenticatable,CanResetPasswordContract

{
    use SoftDeletes;
    use AuthenticableTrait;
    use HasApiTokens, Notifiable;
    use CanResetPassword;


    protected $table = 'users';
    protected $primaryKey = '_id';
    protected $dates = ['deleted_at'];
    protected $softDelete = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'surname','username','bio','abstract','country','state','province',
        'email','email_verified','bio','abstract',
        'cover_photo',
        'social',
        'active','password',
        'remember_token', 'profile_photo', 'lang', 'role',
        'like_story_id', 'like_comment_id', 'follow_user_id', 'followers','membership_active','web_store',
        'searches','verify_token', 'resources', 'favourite_genres','newsletter'
    ];


    /**
     * Get Membership meta.
     *
     * @return mixed
     */

    public function membership()
    {
        return $this->embedsMany('App\Membership');
    }


    /**
     * Get Membership meta.
     *
     * @return mixed
     */

    public function notification()
    {
        return $this->embedsMany('App\Notification');
    }


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['profile_photo','cover_photo','password','social','membership_active'];





    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
        //return $this->attributes['_id'];
    }

    /**
     * Return true if role is admin.
     *
     * @return mixed
     */

    public function isAdmin(){
        return in_array('admin',$this->role);

    }

    /**
     * Return Obj Story.
     *
     * @return mixed
     */
    public function likeStories(){
       return Story::wherein('_id', $this->like_story_id)->get();
    }


    /**
     * Get the value profile_photo Base64.
     *
     * @return mixed
     */
    public  function getProfilePhoto()
    {
       return $this->profile_photo;
    }

    /**
     * Get the value cover_photo Base64.
     *
     * @return mixed
     */
    public  function getCoverPhoto()
    {
        return $this->cover_photo;
    }

    /**
     * Get the value email.
     *
     * @return mixed
     */
    public  function getEmail()
    {
        return $this->email;
    }


    /**
     * Get the value Lang.
     *
     * @return mixed
     */
    public  function getLang()
    {
        return $this->lang;
    }



    /**
     * Get the value password.
     *
     * @return mixed
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Return Obj full user.
     *
     * @return mixed
     */
    public static function infoComplete($id)
    {
        try {
            $user = User::where('_id', $id)->firstOrFail();
            return $user;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Return Obj user without unset fields.
     *
     * @return mixed
     */
    public function info(){
        try {
            $user=$this;
            $user->membership=$user->membership()->get();
            $notification=$user->notification()->get();
            $notification = $notification->map(function ($not) {

               if ($not->subject()->first()){
                   $not->subject=$not->subject()->first()->infoSmall();
                   return $not;
               }
            });
            $user->notification=$notification;
            if(!isset($user->web_store)){
                $wb= new \stdClass();
                $wb->url=null;
                $wb->verified=false;
                $user->web_store=$wb;
            }


            return $user;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Return Obj user without unset fields.
     *
     * @return mixed
     */
    public function infoSmall(){
        try {
            $user=$this;
            unset($user->updated_at);
            unset($user->created_at);
            unset($user->social);
            unset($user->abstract);
            unset($user->password);
            unset($user->remember_token);
            unset($user->verify_token);
            unset($user->notification);
            unset($user->membership_active);
            unset($user->newsletter);
            unset($user->role);
            unset($user->email_verified);
            unset($user->email);
            unset($user->searches);
            unset($user->favourite_genres);
            if(!isset($user->web_store)){
                $wb= new \stdClass();
                $wb->url=null;
                $wb->verified=false;
                $user->web_store=$wb;
            }


            return $user;
        } catch (Exception $e) {
            return false;
        }
    }



    public function notifyInfo(){
        try {
            $u=new \stdClass();
            $u->name=$this->name. " ". $this->surname;
            $u->_id=$this->_id;
            return $u;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Check email user and return true or false.
     *
     * @return mixed
     */
    protected function checkEmail($email){
        try {
            return User::where('email', $email)->get()->isNotEmpty();
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Check username user and return true or false.
     *
     * @return mixed
     */
    protected function checkUsername($username){
        try {
            return User::where('username', $username)->get()->isNotEmpty();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Return an array label errors or validated.
     *
     * @return mixed
     */
    protected function validatorUser(array $data ,$method)
    {

        $rules = "";
        $data['id']=empty($data['id']) ? '' : $data['id'];

        switch($method)
        {
            case 'POST':
            {
                $rules = [
                    'name'     => 'required|min:3|max:100',
                    'surname'  => 'min:3|max:200',
                    'email'    => 'required|unique:users,email',
                    'password' => 'required|min:6|max:24',
                    'username' => 'required|unique:users|min:6|max:24'
                ];
            }
            break;

            case 'PUT':
            {
                $rules = [
                    'name'     => 'required|min:3|max:100',
                    'surname'  => 'min:3|max:200',
                    'email'    => 'required|unique:users,email_address,'.$data['id'],
                    'password' => 'required|min:6|max:24',
                    //'username'=> 'required|unique:users|min:6|max:24,'.$data['id'],
                    'username' => 'required|unique:username,' . $data['id']
                ];
            }
            break;
        }

        $messages = [
            'required'  => strtoupper(':attribute_is_required'),
            'unique'    => strtoupper(':attribute_busy'),
            'email'     => strtoupper(':attribute_invalid'),
            'max'       => strtoupper(':attribute_too_long'),
            'min'       => strtoupper(':attribute_too_short')
        ];

        $validator = \Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        return "validated";
    }


    /**
     * Add or remove a Story_id to like_story_id.
     *
     * @return mixed
     */
    public function setLikes($id, $model, $action)
    {
        switch($model)
        {
            case 'story':
            {
                $list_id=$this->like_story_id;
                if ($action==="like"){
                    $list_id[]=$id;
                }else{
                    $list_id = array_diff($list_id, array($id));
                }
                $this->like_story_id=$list_id;
                $this->save();
                return true;
            }
                break;


            case 'comment':
            {
                $list_id=$this->like_comment_id;
                if ($action==="like"){
                    $list_id[]=$id;
                }else{
                    $list_id = array_diff($list_id, array($id));
                }
                $this->like_comment_id=$list_id;
                $this->save();
                return true;
            }
                break;

            default: break;
        }
    }


    /**
     * Add User_id to follow_MODEL_id.
     *
     * @return mixed
     */
    public function setFollowers($id, $model, $action)
    {
        switch($model)
        {
            case 'user':
                if($this->_id != $id){
                    $list_id=$this->follow_user_id;
                    if ($action==="follow"){
                        $list_id[]=$id;
                    }else{
                        $list_id =array_splice($list_id, 1, 1);
                    }

                    $this->follow_user_id=$list_id;
                    $this->save();
                    return true;
                }
                break;

            default: break;
        }
    }


    /**
     * Add or Subtract 1 likes field
     *
     * @return mixed
     */

    public function setTotalFollowers($operator)
    {
        try {
            $this->followers= ($operator=="+")? $this->followers+1 :$this->followers-1;
            $this->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check value into array story.
     *
     * @return mixed
     */
    public function checkArrayId($id, $model)
    {
        switch($model) {
            case 'story':
                if (in_array($id, $this->like_story_id)) {
                    return true;
                }
                return false;
                break;

            case 'comment':
                if (in_array($id, ($this->like_comment_id)?$this->like_comment_id : array())) {
                    return true;
                }
                return false;
                break;

            case 'user':
                if (in_array($id, $this->follow_user_id)) {
                    return true;
                }
                return false;
                break;

            default: break;
        }
    }



    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        try {
            //$headers = apache_request_headers();
            $url= env('APP_FRONT_URL');
            $this->notify(new NotifyUser($this, $url,'resetPsw',$token));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check user required attributes and set active if valid user
     *
     * @param  string  $token
     * @return void
     */
    public function setActive()
    {
        if (empty($this->name  || $this->email || $this->username || $this->email_verified != null))
        {
            $this->active=0;
        }
        $this->active=1;
    }



    /**
     * Check User Trashed from Passport.
     *
     * @param  string  $token
     * @return void
     */
    public function findForPassport($username)
    {
        try {
            return $this->withTrashed()->where('email', $username)->first();
        } catch (Exception $e) {
            return false;
        }
    }

}