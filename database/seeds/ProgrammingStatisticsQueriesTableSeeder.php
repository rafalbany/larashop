<?php

use Illuminate\Database\Seeder;

class ProgrammingStatisticsQueriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('programming_statistics_queries')->insert(['lang' => 'PHP', 'query' => 'php+-front%2C+-frontend%2C+-c%23%2C+-java%2C+-c%2B%2B&l=']);
        DB::table('programming_statistics_queries')->insert(['lang' => 'C#', 'query' => 'c%23+-front%2C+-frontend%2C+-php%2C+-java%2C+-c%2B%2B&l=']);
        DB::table('programming_statistics_queries')->insert(['lang' => 'C++', 'query' => 'c%2B%2B+-front%2C+-frontend%2C+-php%2C+-java%2C+-c%23&l=']);
        DB::table('programming_statistics_queries')->insert(['lang' => 'Java', 'query' => 'java+-front%2C+-frontend%2C+-php%2C+-c%2B%2B%2C+-c%23&l=']);
    }
}
