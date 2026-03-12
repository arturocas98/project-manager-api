<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run(): void
    {
        if (DB::table('menu_items')->exists()) {
            return;
        }

        $now = now()->toDateTimeString();

        DB::table('menu_items')->upsert([
            [
                'id' => 1,
                'menu_id' => 1,
                'link_id' => 4,
                'sequence' => 1,
                'parent_menu_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'menu_id' => 1,
                'link_id' => 2,
                'sequence' => 2,
                'parent_menu_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'menu_id' => 1,
                'link_id' => 3,
                'sequence' => 3,
                'parent_menu_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'menu_id' => 1,
                'link_id' => 1,
                'sequence' => 4,
                'parent_menu_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'menu_id' => 1,
                'link_id' => 5,
                'sequence' => 5,
                'parent_menu_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'menu_id' => 2,
                'link_id' => 4,
                'sequence' => 6,
                'parent_menu_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'menu_id' => 2,
                'link_id' => 5,
                'sequence' => 7,
                'parent_menu_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'menu_id' => 3,
                'link_id' => 4,
                'sequence' => 8,
                'parent_menu_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 9,
                'menu_id' => 1,
                'link_id' => 6,
                'sequence' => 9,
                'parent_menu_item_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], 'id');
    }
}
