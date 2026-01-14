<?php

// php artisan db:seed --class=AirportSeeder
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

// php artisan db:seed --class=AirportSeeder
class AirportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('data/airports.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            DB::table('airports')->insert([
                'name' => $record['name'],
                'code' => $record['code'],
                'time_zone' => $record['time_zone'],
                'city_code' => $record['city_code'],
                'country' => $record['country'],
                'city' => $record['city'],
                'state' => $record['state'],
                'county' => $record['county'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
