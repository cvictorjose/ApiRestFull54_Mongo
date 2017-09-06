<?php

namespace App\Http\Controllers;


use App\Notification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate as GatePermission;


class NotificationController extends Controller
{
    /**
     * @var string
     */
    protected $method;
    public $instance = false;

    public function __construct (Request $request){
        //$this->middleware('auth:api', ['only' => ['show','markAsRead']]);
    }


    /**
     * Display the specified resource.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            if(Auth::user()->_id == $id || Auth::user()->isAdmin()){
                $user = User::where('_id',  $id)->first();
                $notification=$user->notification()->get();
                $notification = $notification->map(function ($not) {
                    if ($not->subject()->first()){
                        $not->subject=$not->subject()->first()->infoSmall();
                        return $not;
                    }
                });
                return $this->createMessage($notification,"200");

            }else{
                abort(403, 'FORBIDDEN');
            }

        }
        catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(Request $request,$id)
    {
        try {
            if(Auth::user()->_id == $id || Auth::user()->isAdmin()){
                $user = User::find($id);
                $notifications=$request->all();
                if(!empty($notifications))
                    foreach($notifications as $n){
                        $user->notification()->where('_id',$n['_id'])->first()->markAsRead();
                    }
                $notification=$user->notification()->get();
                $notification = $notification->map(function ($not) {
                    if ($not->subject()->first()){
                        $not->subject=$not->subject()->first()->infoSmall();
                        return $not;
                    }
                });
                return $this->createMessage($notification,"200");

            }else{
                abort(403, 'FORBIDDEN');
            }


        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }
}

