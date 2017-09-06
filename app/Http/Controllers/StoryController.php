<?php

namespace App\Http\Controllers;


use App\Membership;
use App\Notifications\NotifyStory;
use App\Story;
use App\User;
use App\Book;
use Carbon\Carbon;
use DateTime;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate as GatePermission;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use Dawson\AmazonECS\AmazonECSFacade as Amazon;


class StoryController extends Controller
{



    public function __construct (Request $request){
        $this->middleware('auth:api', ['only' => ['store','update','delete']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function home(Request $request)
    {
        try {
            $page=0;
            $size=50;
            if(Input::get('p'))
                $page=Input::get('p');

            if(Input::get('s'))
                $page=Input::get('s');

            $skip=$page*$size;
            $stories=new \stdClass();

            //Featured
            $stories->story=Story::whereNotNull('featured_position')->orderBy('featured_position')->get();
            $featured_id=$stories->story->pluck('_id');
            if(count($featured_id)<$size){
                $stories->story=$stories->story->merge(Story::whereNotIn('_id',$featured_id)->skip(count($featured_id))->take(($size-count($featured_id)))->get());
                $featured_id=$stories->story->pluck('_id');
            }



            //OnTop1
            /*$userMember=User::where('membership_active','on_top')->pluck('_id');
            if(!empty($userMember)){
                $stories->on_top=Story::whereIn('user_id',$userMember)->whereNotIn('_id',$featured_id)->take(50)->get();
            }else{
                $stories->on_top=array();
            }*/
            if($page==0){
                $group=Story::where('promoted_content.name','on_top_1')->where('promoted_content.end_date','>', (new Carbon())->timestamp)->take(12)->get()->shuffle();
                foreach($group->split(2) as $g)
                    $stories->promoted[]=$g->values();
                $group=Story::where('promoted_content.name','on_top_2')->where('promoted_content.end_date','>', (new Carbon())->timestamp)->take(12)->get()->shuffle();
                foreach($group->split(2) as $g)
                    $stories->promoted[]=$g->values();
            }else{
                $group=Story::whereIn('promoted_content.name',array('on_top_1','on_top_2','on_top_3'))->where('promoted_content.end_date','>', (new Carbon())->timestamp)->take(24)->get()->split(4);
                foreach($group->split(4) as $g)
                    $stories->promoted[]=$g->values();
                $stories->story=Story::whereNotIn('_id',$featured_id)->skip($skip)->take($size)->get();
            }
           /* $membership_id=$stories->on_top->pluck('_id');*/

            //Generic



            if(isset($stories->story)){
                foreach ($stories->story as $st){
                    $st=$st->info();
                }
            }


           if(!empty($stories->promoted)){
                foreach ($stories->promoted as $sto){
                    if (count($sto)>1){
                        foreach($sto as $st)
                            $st=$st->info();
                    }

                }
            }

            /*$stories->promoted=$stories->promoted->split(4)->values();*/
            return $this->createMessage($stories,"200");

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        try {
            $input = $request->only(['user_id','genre_id','type','string','page','size','lang','filter','source']);

            $ord=false;
            $page=1;
            $size=50;
            $query = Story::Select();
            if (!empty($input)) {

                foreach($input as $column => $value)
                {
                    if($value!=null)
                    switch ($column){
                        case "user_id":
                            $query->where('user_id',$value);
                            break;
                        case "genre_id":
                            $query->where('genre_id',$value);
                            break;
                        case "type":
                            $query->where('type', $value);
                            break;
                        case "source":
                            $query->where('source', $value);
                            break;
                        case "lang":
                            $query->where('lang', $value);
                            break;
                        case "page":
                            $page=(int)$value;
                            break;
                        case "size":
                            $size=(int)$value;
                            break;
                        case "filter":
                            switch ($value){
                                case "featured":
                                    $ord=true;
                                    $query->whereNotNull('featured_position');
                                    break;
                                case "not_featured":
                                    $query->whereNull('featured_position');
                                    break;
                                default:
                                   /* if(Membership::where('name',$value)->get()){
                                        $userMember=User::where('membership_active',$value)->pluck('_id');
                                        if(!empty($userMember)){
                                            $query->whereIn('user_id',$userMember);
                                        }
                                    }*/
                                    $query->where('promoted_content.name',$value)->where('promoted_content.end_date','>', new Carbon());
                            }
                            break;
                        case "string":
                            break;
                        default:
                            break;
                    }
                }
            }
            $skip=$size*($page-1);
            if($ord)
                $query->orderBy('featured_position','asc');
            else
                $query->orderBy('created_at','desc');

            $stories = $query->skip($skip)->take($size)->get();

           if(count($stories)){
                foreach ($stories as $story){
                    $story=$story->info();
                }
            }
            $result=new \stdClass();
            $result->totalHits=$stories->count();
            $result->maxScore=0;
            $result->timedOut=0;
            $result->took=0;
            $result->aggregations='';
            $result->stories=$stories;
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
            $stories = Story::all();
            if(count($stories)){
                foreach ($stories as $story){
                    $story=$story->info();
                }
                return $this->createMessage($stories,"200");
            }

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

    public function show(Request $request, $id)
    {
        try {

            $story = Story::find($id);
            if(count($story)){
                $pattern = '/(facebookexternalhit|Facebot|Twitterbot|GoogleBot|Google|Bingbot|Slurp|DuckDuckBot)/i';
                $agent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_ENCODED);
                if(preg_match($pattern,$agent)){
                    return View('social.story')->with(array('story'=> $story));
                }else{
                    return $this->createMessage($story->info(),"200");
                }
            }
            abort(404, 'NOT_FOUND');

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
        try {

            //$this->authorize('create',Story::class);
            if(GatePermission::denies('create', Story::class)){ abort(403, 'FORBIDDEN');}

            $rules = [
                'type'       => 'required',
                'source'     => 'required',
                'genres'     => 'required',
                'user._id'   => 'required',
                'lang'       => 'required'
            ];

            $validator = Validator::make(Input::all(), $rules);
            if ($validator->fails())
            {
                return $this->createMessageError($validator->errors()->all(),"400");
            }

            $new_photo   = empty(Input::get('cover_photo')) ? null : $this->getValuePhoto(Input::get('cover_photo'));
            $lang        = empty(Input::get('lang'))        ? Auth::user()->lang : (string) Input::get('lang');

            $genres=Input::get('genres');
            $genres_id=array_values(array_column($genres,'_id'));

            $story=Story::create(array(
                'name'           => Input::get('name'),
                'user_id'        => Input::get('user._id'),
                'body'           => usernameToId(Input::get('body')),
                'cover_photo'    => $new_photo,
                'lang'           => $lang,
                'type'           => Input::get('type'),
                'source'         => Input::get('source'),
                'genre_id'       => $genres_id,
                'microformat'    => Input::get('microformat'),
                'likes'          => 0
            ));

            if(Input::get('type')=='book'){
                if(strlen(Input::get('book.ASIN'))!=10)
                    throw new Exception('ASIN_INVALID',400);
                $book=Book::checkBook(Input::get('book.ASIN'));
                if(!$book){
                    $product = Amazon::lookup(Input::get('book.ASIN'))->json();

                    if(!isset($product['Items']['Request']['Errors']['Error'])){
                        $item=$product['Items']['Item'];
                        $book=Book::create(array(
                            'ASIN'              => $item['ASIN'],
                            'url'               => $item['DetailPageURL'],
                            'title'             => $item['ItemAttributes']['Title'],
                            'author'            => $item['ItemAttributes']['Author'],
                            'thumb_url'         => $item['MediumImage']['URL'],
                            'cover_photo_url'   => (isset($item['ImageSets']['ImageSet']['HiResImage']['URL']))?$item['ImageSets']['ImageSet']['HiResImage']['URL']:$item['LargeImage']['URL'],
                            'price'             => (isset($item['ItemAttributes']['ListPrice']['FormattedPrice']))?$item['ItemAttributes']['ListPrice']['FormattedPrice']:$item['OfferSummary']['LowestNewPrice']['FormattedPrice'],
                            'feed'              => (object)$item
                        ));
                    }
                }
                $story->book()->save($book->replicate());

            }


            if(Input::get('source')=='vow'){
                $owner = Auth::user()->getAuthIdentifier();
                $myFollowers= User::where('follow_user_id',$owner)->get();

                if ($myFollowers){
                    foreach ($myFollowers as $f){
                        $f->notify(new NotifyStory($story,false,'create', $owner, false));
                    }
                }
            }

            $this->moveFileToMedia($story->_id);

            return $this->createMessage($story->info(),"200");

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
     */
    public function update(Request $request, $id)
    {
        try {
            $story = Story::find($id);
            //$this->authorize('update', $story);
            if(GatePermission::denies('update', $story->user()->first())){ abort(403, 'FORBIDDEN');}


            $rules = [
                'type'       => 'required',
                'source'     => 'required',
                'genres'     => 'required',
                'user._id'   => 'required',
                'lang'       => 'required'
            ];

            $validator = Validator::make(Input::all(), $rules);
            if ($validator->fails())
            {
                return $this->createMessageError($validator->errors(),"400");
            }

            $input = $request->only(['name','body','cover_photo','type','source','genres','lang','microformat','book']);

            foreach($input as $column => $value)
            {
                if($value!=null)
                    switch ($column){
                        case "genres":
                            $story->genre_id=array_values(array_column($value,'_id'));
                            break;
                        case "cover_photo":
                            $story->cover_photo = ($value && $value != '') ? $this->getValuePhoto($value) : $story->cover_photo;
                            break;
                        case "book":
                            if($value['ASIN']){
                                if(!$story->book()->where('ASIN', $value['ASIN'])->first()){
                                    $story->book()->delete();
                                    $book=Book::checkBook($value['ASIN']);
                                    if(!$book){
                                        $product = Amazon::lookup(Input::get('book.ASIN'))->json();

                                        if(!isset($product['Items']['Request']['Errors']['Error'])){
                                            $item=$product['Items']['Item'];
                                            $book=Book::create(array(
                                                'ASIN'              => $item['ASIN'],
                                                'url'               => $item['DetailPageURL'],
                                                'title'             => $item['ItemAttributes']['Title'],
                                                'author'            => $item['ItemAttributes']['Author'],
                                                'thumb_url'         => $item['MediumImage']['URL'],
                                                'cover_photo_url'   => (isset($item['ImageSets']['ImageSet']['HiResImage']['URL']))?$item['ImageSets']['ImageSet']['HiResImage']['URL']:$item['LargeImage']['URL'],
                                                'price'             => (isset($item['ItemAttributes']['ListPrice']['FormattedPrice']))?$item['ItemAttributes']['ListPrice']['FormattedPrice']:$item['OfferSummary']['LowestNewPrice']['FormattedPrice'],
                                                'feed'              => (object)$item
                                            ));
                                        }
                                    }
                                    $story->book()->save($book->replicate());
                                }
                            }
                            break;
                        default:
                            $story->{$column} = $value;
                            break;
                    }
            }

            empty(Input::get('lang')) ? $story->lang=Auth::user()->lang : '';
            $story->save();
            $newPosition=(int)Input::get('featured_position');
            if($newPosition && $newPosition!=$story->featured_position && Auth::user()->isAdmin()){
                $story->featured_position=($newPosition-0.5);
                $story->save();
                $stories=Story::whereNotNull('featured_position')->orderBy('featured_position','asc')->get();
                $stories->each(function ($item, $key) {
                    if($key<50){
                        $item->featured_position=$key+1;
                        $item->save();

                    }else
                        $item->unset('featured_position');
                });
                $story=Story::find($story->_id);
            }

            return $this->createMessage($story->info(),"200");

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $story = Story::where('_id', $id)->firstOrFail();
            //$this->authorize('delete', $result);
            if(GatePermission::denies('delete', $story)){ abort(403, 'FORBIDDEN');}
            $story->forceDelete();
            $story->removeIdStory($id);
            return $this->createMessage('DELETED_STORY',"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }


    /**
     * Display # Random Stories.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function related($id)
    {
        try {
            $story=Story::find($id);

            if($story){
                //related POST
                $related_category= Story::where('_id','!=',$id)->where('type','post')->whereIn('genre_id',$story->genre_id)->get()->take(8);
                $related_user= Story::where('_id','!=',$id)->where('type','post')->where('user_id',$story->user_id)->get()->take(8);

                $related_post=$related_category->merge($related_user);
                if($related_post->count()>4)
                    $related_post=$related_post->shuffle()->take(4);
                else{
                    $related_find=$related_post->pluck('_id');
                    $related_post->merge(Story::where('_id','!=',$id)->whereNotIn('_id',$related_find)->take(8)->get());
                    $related_post=$related_post->shuffle()->take(4);
                }
                if(count($related_post)){
                    foreach ($related_post as $stor){
                        $stor=$stor->info();
                    }
                }

                //related BOOK
                $relatedBook_category= Story::where('_id','!=',$id)->where('type','book')->where('user_id',$story->user_id)->whereIn('genre_id',$story->genre_id)->get()->take(8);
                $relatedBook_user= Story::where('_id','!=',$id)->where('type','book')->where('user_id',$story->user_id)->get()->take(8);
                $related_book=$relatedBook_category->merge($relatedBook_user);
                $related_book=$related_book->shuffle()->take(4);
                if(count($related_book)){
                    foreach ($related_book as $stor){
                        $stor=$stor->info();
                    }
                }
                $result=new \stdClass();
                $result->books=$related_book;
                $result->posts=$related_post;
                return $this->createMessage($result,"200");

            }else{
                abort(404, 'NOT_FOUND');
            }

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }

    /**
     * Promote story
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function promote(Request $request, $id)
    {
        try {
            $story=Story::find($id);
            if(GatePermission::denies('update', $story->user()->first())){ abort(403, 'FORBIDDEN');}
            $membership = $story->user()->first()->membership()->where('_id', Input::get('_id'))->first();

            if (!$membership)
                abort(400, 'MEMBERSHIP_NOT_VALID');
            if($membership->credits<1)
                abort(400, 'NOT_ENOUGH_CREDITS');

            if(isset($story->promoted_content)){
                $pc=$story->promoted_content;
               if(count($pc))
                    foreach ($pc as $item) {
                        if(/*$item->name==$membership->name && */$item['end_date']>(new Carbon())->timestamp)
                            abort(400, 'POST_ALREADY_PROMOTED');
                    }
            }else
                $pc=array();

            $pcNew=new \stdClass();
            $pcNew->name=$membership->name;
            $pcNew->end_date=(new Carbon('+1 week'))->timestamp;
            $pc[]=$pcNew;
            $story->promoted_content=$pc;
            $story->save();
            $membership->decrement('credits');
            $response=new \stdClass();
            $response->promoted_content=$pc;
            $response->memberships=$story->user()->first()->membership();

            return $this->createMessage($response,"200");

        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }
    }



    /**
     * Get list of Tags For autocomplete.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function autocompleteTags()
    {
        /*try {
            $string=Input::get('s');
            if($string!='') {
                $params = array(
                    'index' => 'theland',
                    'type' => 'story',
                    'body' => array(
                        '_source' => 'tag_suggest',
                        'suggest' => array(
                            'tagsSuggest' => array(
                                'text' => $string,
                                'completion' => array('field' => 'tag_suggest', 'size' => 500),
                            ),
                        )
                    )
                );
                $instance = new Story;
                $r = $instance->getElasticSearchClient()->search($params);
                $temp = $r['suggest']['tagsSuggest'][0]['options'];
                $result = array();

                foreach ($temp as $t) {
                    $flag = false;
                    if (!empty($result)) {
                        foreach ($result as $rs) {
                            if ($rs->value == $t['text']) {
                                $rs->count++;
                                $flag = true;
                            }
                        }
                    }
                    if (!$flag) {
                        $tp = new \stdClass();
                        $tp->value = $t['text'];
                        $tp->count = 1;
                        array_push($result, $tp);
                    }
                }
            }else{
                $result=array();
            }
            return $this->createMessage($result,"200");
        } catch (\Exception $e) {
            return $this->createCodeMessageError($e);
        }*/
        return false;
    }


}
