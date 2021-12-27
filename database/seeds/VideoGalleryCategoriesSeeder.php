<?php

use Illuminate\Database\Seeder;
use App\VideoGalleryCategory;

class VideoGalleryCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        VideoGalleryCategory::insert([
            [
                'title' => 'მიმოხილვა',
            ],
            [
                'title' => 'დოკუმენტური ფილმები',
            ],
            [
                'title' => 'ზოგადი ვიდეოები',
            ],
            [
                'title' => 'სპორტი',
            ],
        ]);

        VideoGalleryCategory::insert([
            [
                'title' => 'ფეხბურთი',
                'parent_id' => 4,
            ],
            [
                'title' => 'კალათბურთი',
                'parent_id' => 4,
            ],
            [
                'title' => 'ჩოგბურთი',
                'parent_id' => 4,
            ],
            [
                'title' => 'რაგბი',
                'parent_id' => 4,
            ],
            [
                'title' => 'UFC',
                'parent_id' => 4,
            ],
            [
                'title' => 'სხვა',
                'parent_id' => 4,
            ],
        ]);
    }
}
