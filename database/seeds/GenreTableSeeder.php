<?php

use App\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('genre')->delete();
        //Create an Place
        $names=array("action_adventure",
            "non_fiction",
            "classics_poetry",
            "romance_chicklit",
            "fantasy_science_fiction",
            "general_fiction",
            "short_story",
            "historical_fiction",
            "spiritual",
            "horror_vampire_werewolf",
            "teen_fiction_fanfiction",
            "humor",
            "mystery_thriller_paranormal",
            "other"
        );
        foreach ($names as $n){
            Genre::create([
                'name' => $n
            ]);
        }
    }
}
