<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Notifications\NotifyUser;
use App\Story;
use App\User;
use Illuminate\Support\Facades\Gate as GatePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;


class CommentController extends Controller
{
    /**
     * @var string
     */
    protected $method;
    public $instance = false;

    public function __construct (Request $request){
        $this->method = $request->method();
        $this->middleware('auth:api', ['only' => ['store','update','delete']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all_stories = Story::all();
        $commentList=array();
        foreach($all_stories as $story)
        {
            $commentList[$story->name][$story->_id]=$story->commentsThree();
        }
        return $this->createMessage($commentList,"200");
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $story_id, $parent_id=false)
    {

        $cc_validated=Comment::validatorComment($request->all(),$this->method);

        try {

            if ($cc_validated !="validated") {
                return $this->createMessageError($cc_validated,"404");
            }else{

                $who_commented=Input::get('user._id');

                $new = new Comment([
                    'user_id'   => $who_commented,
                    'text'      => usernameToId(Input::get('text')),
                    'mention_id'=> extractMentions(Input::get('text')),
                    'parent_id' => $parent_id,
                    'active'    => "1",
                ]);

                $story=Story::where('_id', $story_id)->first();
                $result = $story->comments()->save($new);

                if($parent_id){
                    $comment = $story->comments()->where('_id', $parent_id)->first();
                    $owner   = User::find($comment->user_id);
                }else{
                    $owner=User::find($story->user_id);
                }

                $numberComment=$story->comments()->get()->where('parent_id',$parent_id)->where('user_id',$who_commented)->count()-1;

                if(!$numberComment){
                   $owner->notify(new NotifyUser($story, false,'responseComment',$who_commented,false));
                }

                $result->user= $result->user()->first()->infoSmall();
                $result->text= idToUsername($result->text);
                $result->mentions= $result->completeUserMention();
                return $this->createMessage($result,"200");
            }
        }
        catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $story_id, $comment_id)
    {

        $check_data=array('user_id'=>Input::get('user._id'), 'text'=>Input::get('text'));
        $cc_validated=Comment::validatorComment( $check_data,$this->method);

        try {
            if ($cc_validated !="validated") {
                return $this->createMessageError($cc_validated,"404");
            }else{

                $story=Story::where('_id', $story_id)->first();
                $comment = $story->comments()->where('_id', $comment_id)->first();
                if(GatePermission::denies('update', $comment)){ abort(403, 'FORBIDDEN');}

                $comment->text        = usernameToId(Input::get('text'));
                $comment->user_id     = Input::get('user._id');
                $comment->mention_id  = extractMentions(Input::get('text'));
                $comment->save();

                $comment->text    = idToUsername($comment->text);
                $comment->user    = $comment->user()->first()->infoSmall();
                $comment->mentions= $comment->completeUserMention();
                return $this->createMessage($comment,"200");
            }

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $story = Story::where('_id',  $id)->first();
            $commentList=array();
            $commentList[$story->_id]=$story->commentsThree($story->comments);
            //$commentList=$story->commentsThree($story->comments);
            return $this->createMessage($commentList,"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  $story_id, $comment_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($story_id, $comment_id)
    {
        try {
            $story=Story::where('_id', $story_id)->first();
            $comment = $story->comments()->where('_id', $comment_id)->first();
            if(GatePermission::denies('update', $comment)){ abort(403, 'FORBIDDEN');}
            $comment->delete();
            return $this->createMessage($comment,"200");

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }
}

