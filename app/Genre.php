<?php

namespace App;

use Moloquent\Eloquent\Model as Eloquent;

class Genre extends Eloquent
{

    protected $table = 'genre';
    protected $primaryKey = '_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];


}