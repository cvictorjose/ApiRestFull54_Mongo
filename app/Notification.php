<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Moloquent\Eloquent\Model as Eloquent;


class Notification extends Eloquent
{

    protected $table = 'notification';
    protected $primaryKey = '_id';
    protected $dates =['read_date'];
    protected $fillable = [
        'read_date','subject_id','predicate','complement'
    ];
    protected $hidden = [
         'updated_at'
    ];

    public function subject(){
        try {
            return $this->belongsTo(User::class, 'subject_id');
        } catch (Exception $e) {
            return false;
        }
    }

    public function markAsRead(){
        $this->read_date=new Carbon();
        $this->save();
    }


}
