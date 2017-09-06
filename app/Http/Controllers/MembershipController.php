<?php

namespace App\Http\Controllers;

use App\Membership;
use App\Notification;
use App\Notifications\NotifyMembership;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate as GatePermission;

class MembershipController extends Controller
{
    public $instance = false;

    public function __construct(Request $request)
    {
        $this->middleware('auth:api', ['only' => ['store','update','delete']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        try {
            $input = $request->only(['name']);
            $query = Membership::select();

            if (!empty($input['name']))
                $query->where('name', 'like', '%'.$input['name'].'%');
            $result = $query->get();

            return $this->createMessage($result,"200");

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            return $this->createMessage(Membership::all(),"200");

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

        //$this->authorize('create',Genre::class);
        if(GatePermission::denies('create', Membership::class)){ abort(403, 'FORBIDDEN');}

        if (!is_array($request->all())) {
            return ['error' => 'request must be an array'];
        }

        $rules = ['name' => 'required'];

        try {
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->createMessageError($validator->errors()->all(),"404");
            }

            $membership=Membership::create(array(
                'name'          => Input::get('name'),
                'duration'      => Input::get('duration'),
                'credits'       => Input::get('credits'),
                'description'   => Input::get('description'),
                'price'         => Input::get('price'),

            ));

            return $this->createMessage($membership,"200");

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $result = Membership::where('_id', $id)->firstOrFail();
            //$this->authorize('view', $result);

            if(count($result)){
                return $this->createMessage($result,"200");
            }else{
                abort(404, 'NOT_FOUND');
            }

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
     *
     */
    public function update(Request $request, $id)
    {
        $result = Membership::find($id);
        //$this->authorize('update', $result);
        if(GatePermission::denies('update', $result)){ abort(403, 'FORBIDDEN');}

        if (!is_array($request->all())) {
            return ['error' => 'request must be an array'];
        }

        $rules = [
            'name' => 'required',
            'duration' => 'required',
            'price'=>'required
            '];

        try {
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->createMessageError($validator->errors()->all(),"404");
            }

            $result->name = Input::get('name');
            $result->price = Input::get('price');
            $result->duration = Input::get('duration');
            $result->credits = Input::get('credits');
            if(Input::get('description')){
                $result->description = Input::get('description');
            }
            $result->save();
            return $this->createMessage($result,"200");

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function destroy($id)
    {
        try {
            $membership = Membership::where('_id', $id)->firstOrFail();
            //$this->authorize('delete', $result);
            if(GatePermission::denies('delete', $membership)){ abort(403, 'FORBIDDEN');}
            $membership->delete();
            return $this->createMessage('DELETED_MEMBERSHIP',"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Request new user Membership
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function requestMembership(Request $request, $id)
    {
        try {
            $user=User::find($id);
            $smb=Membership::find(Input::get('_id'));
            if(!$smb)
                throw new Exception('PARAMETER_INVALID',400);
            if($user->membership()->where('name',$smb->name)->first())
                throw new Exception('BAD_REQUEST',400);
            if(GatePermission::denies('update', $user)){ abort(403, 'FORBIDDEN');}
            $membership=$smb->replicate();
            $membership->requested_date=new Carbon();
            $user->membership()->save($membership);
            $admins=User::where('role','all',['admin'])->get();
            foreach($admins as $ad){
               $ad->notify(new notifyMembership($user,$membership,'request'));
            }
            return $this->createMessage($user->membership()->all(),"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Request new user Membership
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteMembership(Request $request, $id)
    {
        try {
            $membership_id=Input::get('_id');
            $user=User::find($id);
            if(GatePermission::denies('update', $user)){ abort(403, 'FORBIDDEN');}
            $membership = $user->membership()->where('_id', $membership_id)->first();
            if(in_array($membership->name,$user->membership_active))
                $user->pull('membership_active', $membership->name);
            $membership->delete();
            return $this->createMessage($user->membership()->all(),"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Request new user Membership
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $user_id
     * @return \Illuminate\Http\Response
     */
    public function activateMembership(Request $request, $id)
    {
        try {
            $membership_id=Input::get('_id');
            $user=User::find($id);
            $membership = $user->membership()->where('_id', $membership_id)->first();

            if ($membership){
                if(GatePermission::denies('activateMembership', $membership)){ abort(403, 'FORBIDDEN');}
                $membership->date_start=new Carbon(Input::get('date_start'));
                $membership->date_end=new Carbon(Input::get('date_end'));
                $membership->credits=Input::get('credits');
                $membership->save();
                if(!in_array($membership->name,$user->membership_active))
                    $user->push('membership_active', $membership->name);
                $user->notify(new notifyMembership($user,$membership,'activate'));
                return $this->createMessage($user->membership()->all(),"200");

            } else{
                abort(404, 'NOT_FOUND');
            }

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }






}

