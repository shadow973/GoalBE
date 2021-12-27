<?php

use App\CyberSport\Group;
use Illuminate\Database\Seeder;

class CyberSportGroupsSeeder extends Seeder
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

        for($i = 1; $i <= 191; $i++){
            $stage = 'group';
            $firstPlayerNextGroupId = null;
            $secondPlayerNextGroupId = null;
            $date = '2017-12-16';

            switch($i){
                case $i <=32:
                    $date = '2017-12-09';
                    $stage = 'group';
                    $firstPlayerNextGroupId = 64 + $i;
                    $secondPlayerNextGroupId = $i % 2 ? 65 + $i : $firstPlayerNextGroupId - 1;
                    break;
                case $i > 32 && $i <= 64:
                    $date = '2017-12-10';
                    $stage = 'group';
                    $firstPlayerNextGroupId = 64 + $i;
                    $secondPlayerNextGroupId = $i % 2 ? 65 + $i : $firstPlayerNextGroupId - 1;
                    break;
                case $i > 64 && $i <= 128:
                    $stage = '1/64';
                    $firstPlayerNextGroupId = $i + 64 + (ceil(($i - 64 - 2) / 4) * -2);
                    break;
                case $i > 128 && $i <= 160:
                    $stage = '1/32';
                    break;
                case $i > 160 && $i <= 176:
                    $stage = '1/16';
                    break;
                case $i > 176 && $i <= 184:
                    $stage = '1/8';
                    break;
                case $i > 184 && $i <= 188:
                    $stage = '1/4';
                    break;
                case $i > 188 && $i <= 190:
                    $stage = '1/2';
                    break;
                case $i == 191:
                    $stage = 'final';
                    break;
            }

            $groupsToInsert[] = [
                'name'              => "#{$i}",
                'date'              => $date,
                'stage'             => $stage,
                'next_group_ids'    => $stage == 'group' ? "{$firstPlayerNextGroupId}:{$secondPlayerNextGroupId}" : $firstPlayerNextGroupId,
            ];
        }

        $grandFinalGroups = [
            [
                // 192
                'name'              => 'GF Semifinal',
                'date'              => (new \DateTime),
                'stage'             => 'gf_1/2',
                'next_group_ids'    => null,
            ],
            [
                // 193
                'name'              => 'GF Semifinal',
                'date'              => (new \DateTime),
                'stage'             => 'gf_1/2',
                'next_group_ids'    => null,
            ],
            [
                // 194
                'name'              => 'GF Repechage Semifinal',
                'date'              => (new \DateTime),
                'stage'             => 'gf_repechage_1/2',
                'next_group_ids'    => null,
            ],
            [
                // 195
                'name'              => 'GF Final',
                'date'              => (new \DateTime),
                'stage'             => 'gf_final',
                'next_group_ids'    => null,
            ],
            [
                // 196
                'name'              => 'GF Repechage Final',
                'date'              => (new \DateTime),
                'stage'             => 'gf_repechage_final',
                'next_group_ids'    => null,
            ],
            [
                // 197
                'name'              => 'GF Grand Final',
                'date'              => (new \DateTime),
                'stage'             => 'gf_grand_final',
                'next_group_ids'    => null,
            ],
        ];

        $groupsToInsert = array_merge($groupsToInsert, $grandFinalGroups);

        Group::insert($groupsToInsert);
    }
}
