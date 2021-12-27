<?php

use App\FACup\Group;
use Illuminate\Database\Seeder;

class FACupGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Group::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $groupsToInsert = [];

        for($i = 1; $i <= 63; $i++){
            $stage = '1/32';
            $date = new \DateTime;

            switch($i){
                case $i <= 32:
                    $stage = '1/32';
                    break;
                case $i > 32 && $i <= 48:
                    $stage = '1/16';
                    break;
                case $i > 48 && $i <= 56:
                    $stage = '1/8';
                    break;
                case $i > 56 && $i <= 60:
                    $stage = '1/4';
                    break;
                case $i > 60 && $i <= 62:
                    $stage = '1/2';
                    break;
                case $i == 63 :
                    $stage = 'final';
                    break;
            }

            $groupsToInsert[] = [
                'name'              => "#{$i}",
                'date'              => $date,
                'stage'             => $stage,
                'next_group_ids'    => '',
                'room'              => '',
            ];
        }

        Group::insert($groupsToInsert);
    }
}
