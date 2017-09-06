<?php

namespace App\Http\Controllers;

use App\Genre;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate as GatePermission;

class GenreController extends Controller
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
            $query = Genre::select();

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
            return $this->createMessage(Genre::all(),"200");

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
        if(GatePermission::denies('create', Genre::class)){ abort(403, 'FORBIDDEN');}

        if (!is_array($request->all())) {
            return ['error' => 'request must be an array'];
        }

        $rules = ['name' => 'required'];

        try {
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->createMessageError($validator->errors()->all(),"404");
            }

            $genre=Genre::create(array(
                'name'      => Input::get('name')
            ));

            return $this->createMessage($genre,"200");

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
            $result = Genre::where('_id', $id)->firstOrFail();
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
        $result = Genre::find($id);
        //$this->authorize('update', $result);
        if(GatePermission::denies('update', $result)){ abort(403, 'FORBIDDEN');}

        if (!is_array($request->all())) {
            return ['error' => 'request must be an array'];
        }

        $rules = ['name' => 'required'];

        try {
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->createMessageError($validator->errors()->all(),"404");
            }

            $result->name = Input::get('name');
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
            $genre = Genre::where('_id', $id)->firstOrFail();
            //$this->authorize('delete', $result);
            if(GatePermission::denies('delete', $genre)){ abort(403, 'FORBIDDEN');}
            $genre->delete();
            return $this->createMessage('DELETED_Genre',"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


}

