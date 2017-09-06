<?php

namespace App;

use Moloquent\Eloquent\Model as Eloquent;

class Membership extends Eloquent
{

    protected $table = 'membership';
    protected $primaryKey = '_id';
    protected $dates =['date_start','date_end','requested_date'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','description','duration','price','date_start','date_end','requested_date','credits'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function info(){
        try {
            $m=new \stdClass();
            $m->name=$this->name;
            $m->_id=$this->_id;
            return $m;
        } catch (Exception $e) {
            return false;
        }
    }

}