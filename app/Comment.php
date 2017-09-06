<?php

namespace App;

use Moloquent\Eloquent\Model as Eloquent;
use Moloquent\Eloquent\SoftDeletes;

class Comment extends Eloquent
{
    use SoftDeletes;

    protected $table = 'comment';
    protected $primaryKey = '_id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'text', 'user_id','mention_id','mentions','active','parent_id','likes'
    ];
    protected $hidden = [
        'mention_id','mentions'
    ];

    /**
     * Return an array label errors or validated.
     *
     * @return mixed
     */
    public static function validatorComment(array $data ,$method)
    {

        $rules = "";
        $data['user_id']=empty($data['user_id']) ? '' : $data['user_id'];

        switch($method)
        {
            case 'POST':
            {
                $rules = [
                    'text'       => 'required',
                    'user._id'   => 'required'
                ];
            }
                break;

            case 'PUT':
            {
                $rules = [
                    'text'       => 'required',
                    'user_id'   => 'required',
                    //'user_id'    => 'required|user_id,' . $data['user_id'],
                ];
            }
                break;
        }
        $messages = [
            'required'  => strtoupper(':attribute_is_required'),
            'unique'    => strtoupper(':attribute_busy')
        ];

        $validator = \Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        return "validated";
    }


    public function user(){
        try {
            return $this->belongsTo(User::class, 'user_id');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Put complete info user foreach mention_id
     *
     * @return array
     */
    public function completeUserMention(){
        $mentions=array();
        if (!empty($this->mention_id)){
            foreach ($this->mention_id as $mid){
                array_push($mentions,User::find($mid)->infoSmall());
            }
        }
        return $mentions;
    }


    public function setTotalLikes($operator)
    {
        try {

            if ($this->likes==null || $this->likes==''){
                $this->likes=1;
            }else{
                $this->likes= ($operator=="+")? $this->likes+1 :$this->likes-1;
            }
            $this->save();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }


    public function notifyInfo(){
        try {
            $u=new \stdClass();
            $u->text=$this->text;
            $u->_id=$this->_id;
            return $u;
        } catch (Exception $e) {
            return false;
        }
    }
}
