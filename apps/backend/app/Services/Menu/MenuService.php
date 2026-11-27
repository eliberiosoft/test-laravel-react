<?php

namespace App\Services\Menu;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class MenuService
{
    /**
     * @return Collection<int, MenuItem>
     */
    public function treeFor(User $user): Collection
    {
        $items = MenuItem::query()
            ->visibleWith($user->getAllPermissions()->modelKeys())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->branch($items->groupBy(fn (MenuItem $item): int => $item->parent_id ?? MENU_ROOT_ID), MENU_ROOT_ID);
    }

    /**
     * @param  BaseCollection<int, Collection<int, MenuItem>>  $itemsByParent
     * @return Collection<int, MenuItem>
     */
    private function branch(BaseCollection $itemsByParent, int $parentId): Collection
    {
        $children = $itemsByParent->get($parentId, new Collection)
            ->map(fn (MenuItem $item): MenuItem => $item->setRelation(
                'children',
                $this->branch($itemsByParent, $item->getKey()),
            ))
            ->reject(fn (MenuItem $item): bool => $this->isEmptyGroup($item));

        return new Collection($children->values()->all());
    }

    private function isEmptyGroup(MenuItem $item): bool
    {
        return $item->route === null && $item->getRelation('children')->isEmpty();
    }
}
