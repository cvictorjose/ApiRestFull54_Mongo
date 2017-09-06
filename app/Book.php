<?php

namespace App;

use Moloquent\Eloquent\Model as Eloquent;

class Book extends Eloquent
{

    protected $table = 'book_cache';
    protected $primaryKey = '_id';
    protected $fillable = [
        'ASIN','url','title','author','cover_photo_url','thumb_url','price','feed'
    ];

    /**
     * Return an array label errors or validated.
     *
     * @return mixed
     */
    public static function validatorBook(array $data)
    {

        $rules = [
            'url'       => 'required',
            'ASIN'      => 'required'
        ];

        $messages = [
            'required'  => strtoupper(':attribute_is_required')
        ];

        $validator = \Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        return "validated";
    }

    public function infoSmall(){
        try {
            $book=$this;
            unset($book->feed);

            return $book;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function checkBook($identifier){
        return Book::where('_id',$identifier)->orWhere('ASIN',$identifier)->first();
    }

}
