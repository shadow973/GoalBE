<?php

use App\Role;
use Illuminate\Database\Seeder;

class CyberSportRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new Role([
            'name' => 'cybersport_admin',
            'display_name' => 'CyberSport Admin',
        ]);
        $user->save();
    }
}
