<?php

use Illuminate\Database\Seeder;

class CSPlayersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $playersToInsert = [];

        for($i = 1; $i <= 256; $i++){
            $playersToInsert[] = [
                'name' => "Player $i",
            ];
        }

        \App\CyberSport\Player::insert($playersToInsert);
    }
}
