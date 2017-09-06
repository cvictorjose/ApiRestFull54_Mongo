<?php

use App\Place;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('place')->delete();
        //Create an Place
        Place::create([
            'name' => "Shape Master",
            'shape' => "New Shape to Admin TheLand",
            'followers' => 0
        ]);
    }
}
