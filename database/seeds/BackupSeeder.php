<?php

use App\Place;
use App\Story;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class BackupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       echo "Start Import User". PHP_EOL;
       DB::table('users')->where('old_id', 'exists', true)->delete();

        $countUser=0;

        //Import User from old database
        $reader = Reader::createFromPath(database_path('seeds/backup/users.tsv'));
        $reader->setDelimiter("\t");
        $results = $reader->fetchAssoc(0);

        foreach ($results as $row) {
            $socials=array();

            if($row['twitterId']!='NULL'){
                $prd=new \stdClass();
                $prd->name='twitter';
                $prd->id=$row['twitterId'];
                $prd->token=null;
                $prd->refreshToken=null;
                $prd->expiresIn=null;
                $prd->avatar=null;
                array_push($socials, $prd);
            }
            if($row['facebookId']!='NULL'){
                $prd=new \stdClass();
                $prd->name='twitter';
                $prd->id=$row['twitterId'];
                $prd->token=null;
                $prd->refreshToken=null;
                $prd->expiresIn=null;
                $prd->avatar=null;
                array_push($socials, $prd);
            }


           $user=User::create([
                'name'           => $row['firstname'],
                'surname'        => $row['lastname'],
                'username'       => $row['username'],
                'description'    => $row['biography'],
                'email'          => $row['email'],
                'email_verified' => null,
                'cover_photo'    => null,
                'social'         =>  $socials,
                'active'         => 1,
                'password'       => bcrypt($row['username']),
                'remember_token' => str_random(10),
                'lang' => "en",
                'role' => array('user'),
                'like_story_id' => array(), 'like_comment_id' => array(),'follow_board_id' => array(), 'follow_user_id' => array(), 'follow_place_id' => array(),
                'old_id' => $row['id'],
                'followers' => 0,
                'searches' => array(),
                'verify_token'  => null

               ]);
            if($user){
                echo ".";
                $countUser++;
            }


        }
        echo " Completed". PHP_EOL;
        echo "Imported ".$countUser." Users". PHP_EOL;

       /** Section import Old Place**/

        echo "Start Import Place". PHP_EOL;
        DB::table('gate')->where('old_id', 'exists', true)->delete();

        $countUser=0;

        //Import User from old database
        $reader = Reader::createFromPath(database_path('seeds/backup/gates.tsv'));
        $reader->setDelimiter("\t");
        $results = $reader->fetchAssoc(0);

        foreach ($results as $row) {
            $address=json_decode($row['address']);

            $emptyShape= json_decode('{"type": "FeatureCollection", "features": [{"type":"Feature","properties":{},"geometry":{"type":"Polygon","coordinates":[]}}]}');



            if($row['areaCoordinates']!='' && count(explode(';',$row['areaCoordinates']))>2){
                $coords=explode(';',$row['areaCoordinates']);
                foreach ($coords as &$c){
                    $c=explode(',',$c);
                }
                $coords[]=$coords[0];

            }else{
                $coords=array();
                $plus=json_decode(file_get_contents('https://plus.codes/api?key=AIzaSyALnT6n2rxuJHFuWVDukfIcnNlfQF1dABE&address='.$row['centralLatitude'].','.$row['centralLongitude'].''));

                $coords[]=array($plus->plus_code->geometry->bounds->northeast->lat,$plus->plus_code->geometry->bounds->northeast->lng);
                $coords[]=array($plus->plus_code->geometry->bounds->southwest->lat,$plus->plus_code->geometry->bounds->northeast->lng);
                $coords[]=array($plus->plus_code->geometry->bounds->southwest->lat,$plus->plus_code->geometry->bounds->southwest->lng);
                $coords[]=array($plus->plus_code->geometry->bounds->northeast->lat,$plus->plus_code->geometry->bounds->southwest->lng);
                $coords[]=array($plus->plus_code->geometry->bounds->northeast->lat,$plus->plus_code->geometry->bounds->northeast->lng);

            }
            $tmp=array();
            foreach ($coords as $co){
                array_push($tmp,array($co[1],$co[0]));
                $emptyShape->features[0]->geometry->coordinates=array($tmp);
                $emptyShape->features[0]->properties=$address;
            }
            $user=User::where('old_id',$row['author_id'])->first();

            $gate=Place::create([
                'name'      => $address->streetName,
                'user_id'   => $user->_id,
                'shape'     => $emptyShape,
                'category'  => 'Backup',
                'old_id' => $row['id'],
                'old_instance' => ($row['instance_id']=='NULL')?'theland':$row['instance_id'],
                'followers' => 0
            ]);

            if($gate){
                echo ".";
                $countUser++;
            }


        }
        echo " Completed". PHP_EOL;
        echo "Imported ".$countUser." Gates". PHP_EOL;

        /** Section import Old Stories**/

        echo "Start Import Stories". PHP_EOL;
        DB::table('story')->where('old_id', 'exists', true)->delete();

        $countUser=0;
        $countFind=0;

        //Import User from old database
        $reader = Reader::createFromPath(database_path('seeds/backup/stories.tsv'));
        $reader->setDelimiter("\t");
        $results = $reader->fetchAssoc(0);

        foreach ($results as $row) {


            $user=User::where('old_id',$row['author_id'])->first();
            $gate=Place::where('old_id',$row['gate_id'])->first();

            /* Get cover photo */
            $new_photo=false;
            
            if($row['coverImage']!='NULL'){
                $url='https://api.theland.me/uploads/story/'.$row['id'].'/coverimage/'.$row['coverImage'];
                $remoteFile=file_get_contents($url);

                $new_photo = new \stdClass();
                $new_photo->content   = GuzzleHttp\Psr7\mimetype_from_filename($url);
                $new_photo->extension = pathinfo($url, PATHINFO_EXTENSION);
                $new_photo->code      = base64_encode( $remoteFile);
            }

            $story=Story::create([
                'name'          => $row['title'],
                'user_id'       => $user->_id,
                'place_id'       => $gate->_id,
                'instance_id'   => $gate->old_instance,
                'cover_photo'   => ($new_photo)?$new_photo:null,
                'body'          => $row['body'],
                'tags'          => array(),
                'mention_id'    => array(),
                'date_start'    => new Carbon(date('Y-m-d', strtotime( $row['rangeStartDate']))),
                'date_end'      => new Carbon(date('Y-m-d', strtotime( $row['rangeEndDate']))),
                'lang'          => 'it',
                'likes'         => 0,
                'old_id'        => $row['id']
            ]);

            if($story){
                echo ".";
                $countUser++;
            }
            $countFind++;


        }
        echo " Completed". PHP_EOL;
        echo "Imported ".$countUser." from  ".$countFind." Stories". PHP_EOL;
    }
}
