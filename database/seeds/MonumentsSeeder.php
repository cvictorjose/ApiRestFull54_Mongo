<?php

use App\Place;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('place')->where('category','OSM')->delete();

        $monuments=json_decode(file_get_contents(database_path('seeds/monuments.geojson')));
        $admin=User::where('name','Admin')->first();
        $emptyShape= json_decode('{"type": "FeatureCollection", "features": []}');

        foreach($monuments->features as $feature){
            $tmpF=$emptyShape;
            $tmpF->features=array($feature);
            if(isset($feature->properties->name)){
                App\Place::create([
                    'name'      => $feature->properties->name,
                    'user_id'   => $admin->_id,
                    'shape'     => $tmpF,
                    'category'  => 'OSM',
                    'followers'  => 0
                ]);
            }
        }

    }
}
