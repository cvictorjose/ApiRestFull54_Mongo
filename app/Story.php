<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Moloquent\Eloquent\Model as Eloquent;
use Moloquent\Eloquent\SoftDeletes;
use MongoDB\BSON\UTCDateTime;


class Story extends Eloquent
{
    use SoftDeletes;
    use Notifiable;

    protected $table = 'story';
    protected $primaryKey = '_id';


    /**
     * Story object attribute
     *
     * @var string name
     * @var string user_id
     * @var string[] genre_id
     * @var object cover_photo
     * @var string body
     * @var string source
     * @var string microformat
     * @var object feed
     * @var string type
     * @var Book[] books
     * @var string[] tags
     * @var string[] mention_id
     * @var string lang
     * @var int likes
     * @var UTCDateTime created_at
     * @var UTCDateTime updated_at
     *
     */


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'user_id','cover_photo','body','source','feed','type','microformat','genre_id','tags','mention_id','lang','likes','created_at', 'updated_at','featured_position','promoted_content'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['cover_photo','mention_id','tags'];

    protected $dates = ['deleted_at'];

    /**
     * ORM Functions
     *
     * @var array
     */



    public function user(){
        try {
            return $this->belongsTo(User::class, 'user_id');
        } catch (Exception $e) {
            return false;
        }
    }

    public function genre(){
        return Genre::whereIn('_id',$this->genre_id)->get();

    }

    /**
     * Get book meta.
     *
     * @return mixed
     */

    public function book()
    {
        return $this->embedsMany('App\Book');
    }

    /**
     * Get the value cover_photo Base64.
     *
     * @return mixed
     */
    public function getCoverPhoto()
    {
        return $this->cover_photo;
    }

    /**
     * Return Obj user without unset fields.
     *
     * @return mixed
     */
    public function info(){
        try {
            $story=$this;
            if($story->user()->first())
                $story->user = $story->user()->first()->infoSmall();
            /*$story->mentions= $story->completeUserMention($story->mention_id);*/
            $story->excerpt= $story->getExcerpt();
            if($story->book()->get()->count())
                $story->book= $story->book()->first()->infoSmall();
            $story->genres= $story->genre();
            $story->comments= $story->commentsThree($story->comments()->get());
            $story->commentsCount= $story->commentsCount();

            return $story;
        } catch (Exception $e) {
            return false;
        }
    }



    public function notifyInfo(){
        try {
            $u=new \stdClass();
            $u->name=($this->name)?$this->name:$this->microformat;
            $u->_id=$this->_id;
            return $u;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Get the excerpt of story.
     *
     * @return mixed
     */
    public  function getExcerpt()
    {
       // return substr(strip_tags($this->body),0,150).'...';
        $s = substr(strip_tags($this->body),0,150);
        return substr($s, 0, strrpos($s, ' ')).'...';
    }


    /**
     * Put complete info user into comments
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function completeUserComments($comments){
        if(!(empty($comments))){
            foreach($comments as &$c){
                $c['user']=User::find($c['user_id']);
                if(isset($c['mention_id'])){
                    $mentions=array();
                    foreach($c['mention_id'] as $mi){
                        $mentions[]=User::find($mi);
                    }
                    $c['mentions']=$mentions;
                }
                if(isset($c['comments']))
                    $c['comments']=$this->completeUserComments($c['comments']);
                else
                    $c['comments']=array();
            }
        }
        return $comments;
    }

    /**
     * get total comment count
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function commentsCount(){
        return $this->comments()->get()->count();
    }

    /**
     * Put complete info user foreach mention_id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function completeUserMention($mentions_id){
        $mentions=array();
        if (!empty($mentions_id)){
            foreach ($mentions_id as $mid){
                if(User::find($mid))
                    array_push($mentions,User::find($mid)->infoSmall());
            }
        }
        return $mentions;
    }


    /**
     * Get the Story Comments.
     *
     * @return mixed
     */

    public function comments()
    {
        return $this->embedsMany('App\Comment');
    }

    /**
     * Create Comments Three
     *
     * @return mixed
     */
    public  function commentsThree($comments)
    {
        $master=array();
        foreach($comments as $c)
        {
            if ($c->parent_id==false){
                $c->comments = array();

                if(isset($c->deleted_at))
                    $this->emptyField($c);

                if($c->user()->first())
                    $c->user = $c->user()->first()->infoSmall();
                else
                    $c->user=null;
               /* $c->text = idToUsername($c->text);
                $c->mentions= $c->completeUserMention();*/
                $master[]=$c;
            }else{

                if(isset($c->deleted_at))
                    $this->emptyField($c);

                if($c->user()->first())
                    $c->user = $c->user()->first()->infoSmall();
                else
                    $c->user=null;
               /* $c->text        = idToUsername($c->text);
                $c->mentions= $c->completeUserMention();*/
                $this->addComment($master, $c);
            }
        }
        return $master;
    }


    /**
     * Set Empty or Array the comment fields
     *
     * @return mixed
     */

    public function emptyField ($field)
    {
        $field->text="";
        $field->mention_id=array();
        $field->mentions=array();
    }



    /**
     * Add new child or master comments
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addComment($master,$comment)
    {
        foreach($master as &$m){
            if(($m->_id)==$comment->parent_id){

                if(!isset($m->deleted_at)){
                    if (empty($comment->comments)){
                        $comment->comments = array();
                    }
                    $m->comments = array_merge($m->comments, array($comment));
                }
            }else{
                if(!empty($m->comments))
                    //dd($m->comments);
                    $this->addComment($m->comments,$comment);
            }
        }
    }


    /**
     * Add or Subtract 1 likes field
     *
     * @return mixed
     */

    public function setTotalLikes($operator)
    {
        try {
            $this->likes= ($operator=="+")? $this->likes+1 :$this->likes-1;
            $this->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Remove Story_id from User and Board
     *
     * @return mixed
     */

    public function removeIdStory($id)
    {
        try {
            User::where('like_story_id','=',$id)->pull('like_story_id', $id);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    




/** RECURSIVE METHOD TO ELIMINATE COMMENTS */
    /**
     * Delete Comments Three
     *
     * @return mixed
     */
    /*public  function deleteCommentsThree($comments, $comment_id)
    {
        foreach($comments as $c)
        {
            if ($c->_id==$comment_id){
                //echo "delete comment: ".$c->text;
                $c->delete();
                $this->deleteSingleComment($comments, $c->_id);
            }
        }
        return "deleted";
    }*/


    /**
     * Delete single Comments Three
     *
     * @return mixed
     */
    /*public  function deleteSingleComment($comments, $comment_id)
    {
        $list=array();
        foreach($comments as $c)
        {
            if ($c->parent_id==$comment_id){
                //echo "   DELETE Single: ".$c->text;
                $c->delete();
                $list[]=$c->_id;
                if(!in_array($c->parent_id,$list))
                    $this->deleteSingleComment($comments, $c->_id);
            }
        }
        return true;
    }*/

/** FINE RECURSIVE METHOD TO ELIMINATE COMMENTS */


}