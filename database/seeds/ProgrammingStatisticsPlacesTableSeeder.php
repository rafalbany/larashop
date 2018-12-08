<?php

use Illuminate\Database\Seeder;

class ProgrammingStatisticsPlacesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('programming_statistics_places')->insert(['link' => 'https://www.indeed.com/jobs?', 'place' => 'USA']);
        DB::table('programming_statistics_places')->insert(['link' => 'https://www.indeed.co.uk/jobs?', 'place' => 'UK']);
        DB::table('programming_statistics_places')->insert(['link' => 'https://www.indeed.fr/jobs?', 'place' => 'France']);
        DB::table('programming_statistics_places')->insert(['link' => 'https://it.indeed.com/jobs?', 'place' => 'Italy']);
        DB::table('programming_statistics_places')->insert(['link' => 'https://de.indeed.com/jobs?', 'place' => 'Germany']);
        DB::table('programming_statistics_places')->insert(['link' => 'https://pl.indeed.com/praca?', 'place' => 'Poland']);
        DB::table('programming_statistics_places')->insert(['link' => 'https://pl.indeed.com/praca?l=warszawa&', 'place' => 'Warsaw']);
        DB::table('programming_statistics_places')->insert(['link' => 'https://www.indeed.co.uk/jobs?l=London&', 'place' => 'London']);
        DB::table('programming_statistics_places')->insert(['link' => 'https://www.indeed.com/jobs?l=New+York&', 'place' => 'New York']);
        DB::table('programming_statistics_places')->insert(['link' => 'https://de.indeed.com/Jobs?l=Berlin&', 'place' => 'Berlin']);
    }
}
