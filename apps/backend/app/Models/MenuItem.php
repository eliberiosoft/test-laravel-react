<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['parent_id', 'permission_id', 'key', 'label', 'route', 'icon', 'sort_order', 'is_active'])]
class MenuItem extends Model
{
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  list<int>  $permissionIds
     */
    #[Scope]
    protected function visibleWith(Builder $query, array $permissionIds): void
    {
        $query->where('is_active', true)
            ->where(fn (Builder $query) => $query
                ->whereNull('permission_id')
                ->orWhereIn('permission_id', $permissionIds));
    }
}
