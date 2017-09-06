<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class SaveSearchController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        //if(GatePermission::denies('create', Story::class)){ abort(403,"403");}
        try {

            $user= User::find($id);
            $list=$user->searches;

            $new_name=Input::get('name');

            if ($new_name == "" || $new_name== null){
                abort(400, 'PARAMETER_REQUIRED');
            }else {

                if (empty($user->searches)){
                    $new_search= $this->createNewSearch($request,$list);
                    $user->searches=(array)$new_search;
                    $user->save();
                    return $this->createMessage($user->searches,"200");
                }else{

                    $find_search= $this->findNameSearch($new_name,$list,"");

                    if ($find_search==false){
                        $new_search= $this->createNewSearch($request,$list);
                        $user->searches= $new_search;
                        $user->save();
                        return $this->createMessage($user->searches,"200");

                    }else{

                        $list= $this->findNameSearch($new_name,$list,"delete");


                        $new_search= $this->createNewSearch($request,$list);
                        $user->searches= $new_search;
                        $user->save();
                        return $this->createMessage($user->searches,"200");
                    }
                }
            }
        }

        catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    public function createNewSearch($request,$list)
    {
        $input = $request->only(['name','user_id','place_id','bbox','string','lang','tags','date']);

        if (!empty($input)) {
            $new=new \stdClass();
            foreach($input as $column => $value)
            {
                if($value!='' && $value!=null){
                    $new->{$column}   = $value;
                }
            }
            $list[]=$new;
            return $list;
         }
    }

    public function findNameSearch($new_name,$list, $case)
    {
        //print_r($list);

        foreach($list as $key =>$value)
        {
            $field = (object) $value;

            if(!isset($field->name))abort(404, 'NOT_FOUND');


            //var_dump($field->name."-".$new_name);
            if ($field->name === $new_name) {

                switch($case)
                {
                    case 'delete':
                    {
                        unset($list[$key]);
                        $new_arr = array_values($list);
                        return $new_arr;
                    }
                        break;


                    case '':
                    {
                        return true;
                    }
                        break;

                    default: array();
                }
            }
        }
        return false;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user= User::find($id);
            $list=$user->searches;
            $new_name=Input::get('name');

            $list= $this->findNameSearch($new_name,$list,"delete");
            //print_r($list);

            if ($list!=false){
                $user->searches= (array)$list;
                $user->save();
                return $this->createMessage($user->searches,"200");
            }else{
                abort(400, 'BAD_REQUEST');
            }


        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }
}
