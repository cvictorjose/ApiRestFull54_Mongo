<?php

namespace App\Jobs;

use App\Story;
use App\User;
use Facebook\Facebook;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Thujohn\Twitter\Facades\Twitter;
use Feeds;


class GetFeedsUserSocial implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $social;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($social)
    {
        $this->social=$social;
    }

    /**
     * Execute the job.
     *
     * @return resdponse
     */
    public function handle()
    {

            $social_requested=$this->social;

            if($social_requested === "all"){
                $social_list=['facebook','twitter','rss'];
            }else{
                $social_list=array($social_requested);
            }
            $users=User::all();

            //Cicle users
            foreach ($users as $user){

                if ($user->resources){

                    foreach ($social_list as $social) {

                        if($user->resources[$social]['page']!=null)
                        switch ($social) {

                            case 'twitter':
                                break;
                                try{
                                    $feed=Twitter::getUserTimeline(['screen_name' => $user->resources[$social]['page'], 'count' => 30, 'format' => 'json']);
                                    $feed=json_decode($feed);

                                    foreach ($feed as $post){

                                        if (!isset($post->retweeted_status) && ($post->text!=null || $post->text!="") && !(Story::where('user_id',$user->_id)->where('feed.id',$post->id)->first())){
                                            $new=array();
                                            $new['name']        = $post->text;
                                            $new['feed']        = $post;
                                            $new['lang']        = isset($post->lang) ? $post->lang : $user->lang;
                                            $new['microformat'] = "Tweet";
                                            $new['body'] ='';
                                            $new['source'] ='twitter';
                                            if(isset($post->entities->media[0]) && $post->entities->media[0]->type=='photo'){
                                                $new['cover_photo'] =$post->entities->media[0]->media_url_https;
                                                $new['body'].='<img class="img-responsive" src="'.$post->entities->media[0]->media_url_https.'" /><br/>';
                                            }

                                            if(isset($post->entities->urls))
                                                foreach($post->entities->urls as $link){
                                                    $new['body'].='<a href="'.$link->url.'"></a><br/>';
                                                }

                                            if(isset($post->extended_entities->media[0]) && $post->extended_entities->media[0]->type=='video')
                                                $new['body'].= ' <div class="embed-responsive embed-responsive-16by9">
                                                                        <video class="embed-responsive-item" controls>
                                                                            <source src="'.end($post->extended_entities->media[0]->video_info->variants)->url.'" type="video/mp4">
                                                                        </video>
                                                                    </div>';
                                            $this->addStoryFromSocial($new,$user);

                                        }

                                    }
                                }
                                catch (Exception $e)
                                {

                                    echo Twitter::error();
                                }
                                break;

                            case 'facebook':

                                $config=Config::get('services.facebook');

                                $fb = new Facebook([
                                    'app_id' => $config['client_id'],
                                    'app_secret' => $config['client_secret'],
                                    'default_graph_version' => 'v2.9',
                                    'default_access_token' => $config['client_id'].'|'.$config['client_secret'],
                                ]);

                                $request = $fb->request(
                                    'GET', '/',array ('id' => $user->resources[$social]['page'])
                                );

                                $page = $fb->getClient()->sendRequest($request)->getGraphPage();

                                if($page->getId()!=$user->resources[$social]['page']){
                                    $response=$fb->get('/'.$page->getId().'/posts?fields=caption,description,message,story,link,properties,feed_targeting,source,type,status_type,attachments{media,type,url,title},name');
                                    $posts =$response->getDecodedBody();


                                    foreach ($posts['data'] as $post){
                                        $post=(object)$post;

                                        if (isset($post->message) && ($post->message!=null || $post->message!="") && !(Story::where('user_id',$user->_id)->where('feed.id',$post->id)->first())){

                                            $new=array();
                                            $new['name']        = '';
                                            $new['feed']        = $post;
                                            $new['lang']        = $user->lang;
                                            $new['microformat'] = $post->type;
                                            $new['body']        = $post->message;
                                            $new['source']      = 'facebook';

                                            if($post->type=='photo' && isset($post->attachments->data[0]->media->image->src)){
                                                $new['cover_photo'] = $post->attachments->data[0]->media->image->src;
                                                $new['body'].='<img class="img-responsive" src="'.$post->attachments->data[0]->media->image->src.'" /><br/>';
                                            }


                                            if($post->type=='video' && isset($post->source)){
                                                if(isset($post->attachments->data[0]->media->image->src))
                                                    $new['cover_photo'] = $post->attachments->data[0]->media->image->src;
                                                $new['body'].= ' <div class="embed-responsive embed-responsive-16by9">
                                                                        <video class="embed-responsive-item" controls>
                                                                            <source src="'.$post->source.'" type="video/mp4">
                                                                        </video>
                                                                    </div>';
                                            }

                                            $this->addStoryFromSocial($new,$user);

                                        }

                                    }
                                }
                                break;

                            case 'rss':

                                $feed = Feeds::make($user->resources[$social]['page']);
                                if (!$feed->error()){

                                    $max = $feed->get_item_quantity();
                                    foreach ($feed->get_items(0,$max) as $post)
                                    {
                                        $id=($post->get_id()!=null) ? $post->get_id() : 'date'.$post->get_date('U');
                                        if($post->get_title() && !(Story::where('user_id',$user->_id)->where('feed.id',$id)->first())){

                                            $new=array();
                                            $new['lang']        = $user->lang;
                                            $new['microformat'] = 'post';
                                            $new['source']      = 'rss';
                                            $new_rss        = new \stdClass();
                                            $new_rss->id    = $id;
                                            $new['name']    = $new_rss->title= $post->get_title();
                                            $new['body']    = $new_rss->content= $post->get_content();
                                            $new['feed']    = $new_rss;

                                            $this->addStoryFromSocial($new,$user);
                                        }
                                    }
                                }
                                break;
                        }
                    }

                }
            }
    }



    public function addStoryFromSocial($data,$user)
    {
        try
        {
            $post=array(
                'name'           => '',
                'user_id'        => $user->_id,
                'body'           => '',
                'cover_photo'    => '',
                'lang'           => '',
                'type'           => 'post',
                'source'         => '',
                'genre_id'       => array(),
                'microformat'    => '',
                'likes'          => 0
            );
            Story::create(array_merge($post,$data));
            return "addedStory";
        }
        catch (Exception $e) {
            return $e;
        }

    }
}
