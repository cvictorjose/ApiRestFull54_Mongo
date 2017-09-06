<?php

use App\User;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */


    public function run()
    {
        static $password;
        //Clear repository
        DB::table('users')->delete();
        DB::table('story')->delete();

        $socials=array();
        /*$providers= array(1=>"facebook", 2=>"google");
      for ($i = 1; $i < 3; $i++){
          $prd=new \stdClass();
          $prd->name=$providers[$i];
          $prd->id=$i;
          $prd->token="token".$i;
          $prd->refreshToken=null;
          $prd->expiresIn=null;
          $prd->avatar=null;
          array_push($socials, $prd);
      }*/

        $newsletter=   '{ "writing_opportunities": 0,"vow": 0}';
        $resource=   '{
                            "facebook": {
                                "page": null,
                                 "public": 0
                            },
                            "twitter": {
                                "page": null,
                                "public": 0
                            },
                            "rss": {
                                "page": null,
                                "public": 0
                            }
                        }';

        $users = array(
            1 => array('name'=>'Admin',  'surname'=> 'Cappelli',  'username'=>'CapelliDesign', 'bio'=> 'Admin TheLand Api', 'email'=> 'dev@cappellidesign.com', 'role'=> 'admin',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/cappellidesign/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": "https://twitter.com/cappellidesign",
                                "public": 1
                            },
                             "rss": {
                                "page": null,
                                "public": 0
                            }
                        }',
                'newsletter'=> '
                            {
                               "writing_opportunities": 0,
                               "vow": 1
                            }
                        ',
                'membership'=> '{
                        "active": 1,
                        "type": "on_top",
                        "start_date": {
                        "date": "2017-03-13 00:00:00.000000",
                            "timezone_type": 3,
                            "timezone": "UTC"
                        },
                        "end_date": {
                        "date": "2017-07-12 12:50:36.000000",
                            "timezone_type": 3,
                            "timezone": "UTC"
                        }
                }',

                ),
            2 => array('name'=>'Paolo',  'surname'=> 'Starace', 'username'=>'pstarace', 'bio'=> 'Admin SciamLab Api', 'email'=> 'paolo@sciamlab.com', 'role'=> 'admin', 'resources'=> $resource, 'newsletter'=> $newsletter, 'membership'=> null),
            3 => array('name'=>'Alessio',  'surname'=> 'Test',  'username'=>'Alessio_test', 'bio'=> 'Admin SciamLab 2', 'email'=> 'alessio@sciamlab.com', 'role'=> 'admin', 'resources'=> $resource, 'newsletter'=> $newsletter, 'membership'=> null),

            4 => array('name'=>'Neil',  'surname'=> 'Gaiman',  'username'=>'neilgaiman', 'bio'=> 'bio neilgaiman', 'email'=> 'neilgaiman@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/neilgaiman/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": "https://twitter.com/neilhimself",
                                "public": 1
                            },
                            "rss": {
                                "page": "http://www.neilgaiman.com/extras/feed_journal.php",
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),


            5 => array('name'=>'Hugh',  'surname'=> 'Howey',  'username'=>'hughhowey', 'bio'=> 'bio hughhowey', 'email'=> 'hughhowey@vow.com', 'role'=> 'user',
                'resources'=> '{
                             "facebook": {
                                "page": null,
                                "public": 0
                            },
                            "twitter": {
                                "page": "https://twitter.com/hughhowey",
                                "public": 1
                            },
                            "rss": {
                                "page": "http://www.hughhowey.com/feed",
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),

            6 => array('name'=>'Joe R',  'surname'=> 'Lansdale',  'username'=>'JoeRLansdale', 'bio'=> 'bio JoeRLansdale', 'email'=> 'JoeRLansdale@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/JoeRLansdale/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": null,
                                "public": 1
                            },
                            "rss": {
                                "page": null,
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),


            7 => array('name'=>'Ty',  'surname'=> 'Tashiro',  'username'=>'tytashiro', 'bio'=> 'bio tytashiro', 'email'=> 'tytashiro@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/dr.ty.tashiro/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": "https://twitter.com/tytashiro",
                                "public": 1
                            },
                            "rss": {
                                "page": null,
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),


            8 => array('name'=>'Irvine',  'surname'=> 'Welsh',  'username'=>'irvinewelshauthor', 'bio'=> 'bio irvinewelshauthor', 'email'=> 'irvinewelshauthor@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/irvinewelshauthor/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": "https://twitter.com/IrvineWelsh",
                                "public": 1
                            },
                            "rss": {
                                "page": null,
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),

            9 => array('name'=>'Alexander',  'surname'=> 'Mc Call Smith',  'username'=>'alexandermccallsmith', 'bio'=> 'bio alexandermccallsmith', 'email'=> 'alexandermccallsmith@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/alexandermccallsmith/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": "https://twitter.com/McCallSmith",
                                "public": 1
                            },
                            "rss": {
                                "page": null,
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),

            10 => array('name'=>'Alain',  'surname'=> 'De Botton',  'username'=>'AlainDeBottonQuotes', 'bio'=> 'bio AlainDeBottonQuotes', 'email'=> 'AlainDeBottonQuotes@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/AlainDeBottonQuotes/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": "https://twitter.com/alaindebotton",
                                "public": 1
                            },
                            "rss": {
                                "page": null,
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),

            11 => array('name'=>'Dean',  'surname'=> 'Koontz',  'username'=>'deankoontzofficial', 'bio'=> 'bio deankoontzofficial', 'email'=> 'deankoontzofficial@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/deankoontzofficial/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": null,
                                "public": 1
                            },
                            "rss": {
                                "page": null,
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),


            12 => array('name'=>'Haruki',  'surname'=> 'Murakami',  'username'=>'harukimurakamiauthor', 'bio'=> 'bio harukimurakamiauthor', 'email'=> 'harukimurakamiauthor@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/harukimurakamiauthor/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": "https://twitter.com/harukimurakami_",
                                "public": 1
                            },
                            "rss": {
                                "page": null,
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),


            13 => array('name'=>'David',  'surname'=> 'Mitchell',  'username'=>'davidmitchellbooks', 'bio'=> 'bio davidmitchellbooks', 'email'=> 'davidmitchellbooks@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/davidmitchellbooks/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": "https://twitter.com/david_mitchell",
                                "public": 1
                            },
                            "rss": {
                                "page": null,
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),

            14 => array('name'=>'Sloane',  'surname'=> 'Crosley',  'username'=>'sloanecrosley', 'bio'=> 'bio sloanecrosley', 'email'=> 'sloanecrosley@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": "https://www.facebook.com/sloanecrosley/",
                                 "public": 1
                            },
                            "twitter": {
                                "page": "https://twitter.com/askanyone",
                                "public": 1
                            },
                            "rss": {
                                "page": null,
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),


            15 => array('name'=>'Joe',  'surname'=> 'Dunthorne',  'username'=>'joedunthorne', 'bio'=> 'bio joedunthorne', 'email'=> 'joedunthorne@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": null,
                                 "public": 0
                            },
                            "twitter": {
                                "page": "https://twitter.com/joedunthorne",
                                "public": 1
                            },
                             "rss": {
                                "page": null,
                                "public": 0
                            }
                            
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),

            16 => array('name'=>'Russell',  'surname'=> 'Blake',  'username'=>'ussellblake', 'bio'=> 'bio ussellblake', 'email'=> 'ussellblake@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": null,
                                 "public": 0
                            },
                            "twitter": {
                                "page":null,
                                "public": 0
                            },
                            "rss": {
                                "page": "http://russellblake.com/feed/",
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),

            17 => array('name'=>'Seth',  'surname'=> 'Godin',  'username'=>'sethgodin', 'bio'=> 'bio sethgodin', 'email'=> 'sethgodin@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": null,
                                 "public": 0
                            },
                            "twitter": {
                                "page":null,
                                "public": 0
                            },
                            "rss": {
                                "page": "http://www.j-walkblog.com/feed/",
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),

            18 => array('name'=>'Scott',  'surname'=> 'Adams',  'username'=>'scottadams', 'bio'=> 'bio scottadams', 'email'=> 'scottadams@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": null,
                                 "public": 0
                            },
                            "twitter": {
                                "page":null,
                                "public": 0
                            },
                            "rss": {
                                "page": "http://feed.dilbert.com/dilbert/blog",
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),


            19 => array('name'=>'Cory',  'surname'=> 'Doctorow',  'username'=>'corydoctorow', 'bio'=> 'bio corydoctorow', 'email'=> 'corydoctorow@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": null,
                                 "public": 0
                            },
                            "twitter": {
                                "page":null,
                                "public": 0
                            },
                            "rss": {
                                "page": "http://craphound.com/feed/",
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),


            20 => array('name'=>'David',  'surname'=> 'Allen',  'username'=>'davidallen', 'bio'=> 'bio davidallen', 'email'=> 'davidallen@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": null,
                                 "public": 0
                            },
                            "twitter": {
                                "page":null,
                                "public": 0
                            },
                            "rss": {
                                "page": "http://gettingthingsdone.com/feed/",
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),

            21 => array('name'=>'David',  'surname'=> 'Brin',  'username'=>'davidbrin', 'bio'=> 'bio davidbrin', 'email'=> 'davidbrin@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": null,
                                 "public": 0
                            },
                            "twitter": {
                                "page":null,
                                "public": 0
                            },
                            "rss": {
                                "page": "http://davidbrin.blogspot.com/feeds/posts/default",
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),


            22 => array('name'=>'J. K',  'surname'=> 'Rowling',  'username'=>'jkrowling', 'bio'=> 'bio jkrowling', 'email'=> 'jkrowling@vow.com', 'role'=> 'user',
                'resources'=> '{
                            "facebook": {
                                "page": null,
                                 "public": 0
                            },
                            "twitter": {
                                "page":null,
                                "public": 0
                            },
                            "rss": {
                                "page": "https://www.jkrowling.com/feed/",
                                "verified": 1
                            }
                        }',
                'newsletter'=> $newsletter, 'membership'=> null),



         );



        for($s = 1; $s < 23; $s++) {
           User::create(array(
                'name'            => $users[$s]['name'],
                'surname'         => $users[$s]['surname'],
                'username'        => $users[$s]['username'],
                'email'           => $users[$s]['email'],
                'email_verified'  => null,
                'bio'             => $users[$s]['bio'],
                'abstract'        => null,
                'cover_photo'     => null,
                'social'          => array(),
                'active'          => 1,
                'password'        => bcrypt('secret'),
                'remember_token'  => str_random(10),
                'profile_photo'   => null,
                'lang'            => 'en',
                'like_story_id'   => array(),
                'like_comment_id' => array(),
                'follow_user_id'  => array(),
                'followers'       => 0,
                'searches'        => array(),
                'role'            => array('user'),
                'verify_token'    => null,
                'favourite_genres'=> array(),
                'resources'       => ($users[$s]['resources'])? json_decode($users[$s]['resources']) : array(),
                'newsletter'      => ($users[$s]['newsletter'])? json_decode($users[$s]['newsletter']) : array(),
                'membership_active'=> array(),

            ));
        }

    }
}
