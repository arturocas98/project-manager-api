<?php

namespace App\Actions\App\Menu;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class SaveMenuAction
{
    public function execute(array $input, ?Menu $menu = null): Menu
    {
        return DB::transaction(function () use ($input, $menu) {
            $menu ??= new Menu;
            $menu->fill([
                'name' => $input['name'],
                'role_id' => $input['role_id'],
            ]);
            $menu->save();
            if ($menu->items->isNotEmpty()) $menu->items()->delete();
            $this->saveLinks($menu->id, $input['links']);

            return $menu;
        });
    }

    private function saveLinks($menuId, $links, $parentItemId = null)
    {
        foreach ($links as $link) {
            $menuItem = MenuItem::updateOrCreate(
                [
                    'menu_id' => $menuId,
                    'link_id' => $link['link_id'],
                ],
                [
                    'menu_id' => $menuId,
                    'link_id' => $link['link_id'],
                    'sequence' => $link['sequence'],
                    'parent_menu_item_id' => $parentItemId,
                ]
            );
            if (isset($link['links'])) {
                $this->saveLinks($menuId, $link['links'], $menuItem->id);
            }
        }
    }
}
