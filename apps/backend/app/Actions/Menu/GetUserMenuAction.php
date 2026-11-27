<?php

namespace App\Actions\Menu;

use App\Models\MenuItem;
use App\Models\User;
use App\Services\Menu\MenuService;
use Illuminate\Database\Eloquent\Collection;

class GetUserMenuAction
{
    public function __construct(private readonly MenuService $menu) {}

    /**
     * @return Collection<int, MenuItem>
     */
    public function execute(User $user): Collection
    {
        return $this->menu->treeFor($user);
    }
}
