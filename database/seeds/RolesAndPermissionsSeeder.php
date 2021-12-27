<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Roles
         */

        $user = new Role([
            'name' => 'user',
            'display_name' => 'User',
        ]);
        $user->save();

        $journalist = new Role([
            'name' => 'journalist',
            'display_name' => 'Journalist',
        ]);
        $journalist->save();

        $contentManager = new Role([
            'name' => 'content_manager',
            'display_name' => 'Content Manager',
        ]);
        $contentManager->save();

        $admin = new Role([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);
        $admin->save();

        /*
         * Permissions
         */

        $articlesCRUD = new Permission([
            'name' => 'articles_crud',
            'display_name' => 'Create, read, update and delete articles',
        ]);
        $articlesCRUD->save();

        $categoriesCRUD = new Permission([
            'name' => 'categories_crud',
            'display_name' => 'Create, read, update and delete categories',
        ]);
        $categoriesCRUD->save();

        $homeCategoriesCRUD = new Permission([
            'name' => 'home_categories_crud',
            'display_name' => 'Create, read, update and delete home categories',
        ]);
        $homeCategoriesCRUD->save();

        $tagsCRUD = new Permission([
            'name' => 'tags_crud',
            'display_name' => 'Create, read, update and delete tags',
        ]);
        $tagsCRUD->save();

        $topTeamsCRUD = new Permission([
            'name' => 'top_teams_crud',
            'display_name' => 'Create, read, update and delete top teams',
        ]);
        $topTeamsCRUD->save();

        $galleryCRUD = new Permission([
            'name' => 'gallery_crud',
            'display_name' => 'Create, read, update and delete gallery photos and videos',
        ]);
        $galleryCRUD->save();

        $tvProgramCRUD = new Permission([
            'name' => 'tv_program_crud',
            'display_name' => 'Create, read, update and delete TV program',
        ]);
        $tvProgramCRUD->save();

        $pollsCRUD = new Permission([
            'name' => 'polls_crud',
            'display_name' => 'Create, read, update and delete polls',
        ]);
        $pollsCRUD->save();

        $slidesCRUD = new Permission([
            'name' => 'slides_crud',
            'display_name' => 'Create, read, update and delete slides',
        ]);
        $slidesCRUD->save();

        $transfersCRUD = new Permission([
            'name' => 'transfers_crud',
            'display_name' => 'Create, read, update and delete transfers',
        ]);
        $transfersCRUD->save();

        $publishArticles = new Permission([
            'name' => 'publish_articles',
            'display_name' => 'Publish articles',
        ]);
        $publishArticles->save();

        /*
         * Attaching permissions to roles
         */

        $journalist->attachPermissions([
            $articlesCRUD,
            $publishArticles,
        ]);

        $contentManager->attachPermissions([
            $categoriesCRUD,
            $homeCategoriesCRUD,
            $tagsCRUD,
            $topTeamsCRUD,
            $galleryCRUD,
            $tvProgramCRUD,
            $pollsCRUD,
            $slidesCRUD,
            $transfersCRUD,
        ]);
    }
}
