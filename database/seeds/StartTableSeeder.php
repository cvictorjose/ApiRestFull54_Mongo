<?php


use App\Comment;
use App\Story;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class StartTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('story')->delete();
        $lorem_ipsum = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque ex tellus, laoreet non lacus id, varius finibus est. Vestibulum massa dui, egestas at libero sit amet, tincidunt tempor dui. Aenean convallis, sapien vitae pulvinar varius, metus metus sodales leo, id interdum leo nulla sit amet augue. Aliquam ultrices pellentesque hendrerit.";

        $mentions = array();
        $users    = User::all();


        $list_story = array(
            1 => array('name'=>'Story 1',  'user'=> 'Admin',    'comments'=>'', 'mentions'=> $mentions),
            2 => array('name'=>'Story 2',  'user'=> 'Admin',    'comments'=>'', 'mentions'=> ''),
            3 => array('name'=>'Story 3',  'user'=> 'Admin',    'comments'=>'', 'mentions'=> $mentions),
            4 => array('name'=>'Story 4',  'user'=> 'Alessio',  'comments'=>'', 'mentions'=> ''),
            5 => array('name'=>'Story 5',  'user'=> 'Alessio',  'comments'=>'', 'mentions'=> ''),
            6 => array('name'=>'Story 6',  'user'=> 'Paolo',    'comments'=>'', 'mentions'=> ''),
            7 => array('name'=>'Story 7',  'user'=> 'Paolo',    'comments'=>'', 'mentions'=> ''),
            8 => array('name'=>'Story 8',  'user'=> 'Admin',    'comments'=>'', 'mentions'=> ''),
            9 => array('name'=>'Story 9',  'user'=> 'Admin',    'comments'=>'', 'mentions'=> ''),
            10=> array('name'=>'Story 10', 'user'=> 'Alessio',  'comments'=>'', 'mentions'=> ''),
            11=> array('name'=>'Story 11', 'user'=> 'Alessio',  'comments'=>'', 'mentions'=> ''),
            12=> array('name'=>'Story 12', 'user'=> 'Paolo',    'comments'=>'', 'mentions'=> ''),
            13=> array('name'=>'Story 13', 'user'=> 'Paolo',    'comments'=>'', 'mentions'=> ''),
            14=> array('name'=>'Story 14', 'user'=> 'Hugh',     'comments'=>'', 'mentions'=> ''),
            15=> array('name'=>'Story 15', 'user'=> 'Neil',     'comments'=>'', 'mentions'=> ''),
            16=> array('name'=>'Story 16', 'user'=> 'Neil',     'comments'=>'', 'mentions'=> ''),
            17=> array('name'=>'Story 17', 'user'=> 'Ty',       'comments'=>'', 'mentions'=> ''),
            18=> array('name'=>'Story 18', 'user'=> 'Haruki',   'comments'=>'', 'mentions'=> ''),
            19=> array('name'=>'Story 19', 'user'=> 'Sloane',   'comments'=>'', 'mentions'=> ''),
        );




        for($s = 1; $s < 20; $s++) {
            $user_story=\App\User::where('name',$list_story[$s]['user'])->first();
            $user_random=$users->random(1)->first();

            $story=Story::create([
                'name'          => $list_story[$s]['name'],
                'user_id'       => $user_story->id,
                'cover_photo'   => null,
                'body'          => $lorem_ipsum,
                'source'        => 'vow',
                'type'          => 'post',
                'microformat'   => 'post',
                'tags'          => ["Vow"],
                'mention_id'    => $mentions,
                'lang'          => 'en',
                'likes'         => 0,
                'genre_id'      => array(),
            ]);


            //$date=new Carbon( '-'.mt_rand(0,3).' days');
            $comment= new Comment([
                'user_id'   =>  $user_random->id,
                'text'      => "Commento master della storia #".$s,
                //'date'      => (new Carbon( '-'.mt_rand(0,10).' days'))->format('Y-m-d H:i:s.u'),
                'mention_id'=> array(),
                'mentions'  => array(),
                'parent_id' => false,
                'active'    => "1",
            ]);

            $story->comments()->save($comment);
        }



    }
}
