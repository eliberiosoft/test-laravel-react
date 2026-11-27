<?php

namespace App\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class UserSearchFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $term = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
        $pattern = '%'.addcslashes($term, '%_\\').'%';

        $query->where(fn (Builder $query) => $query
            ->where('username', 'like', $pattern)
            ->orWhere('email', 'like', $pattern)
            ->orWhereHas('person', fn (Builder $person) => $person
                ->where('identification', 'like', $pattern)
                ->orWhere('first_name', 'like', $pattern)
                ->orWhere('first_surname', 'like', $pattern)
                ->orWhere('second_surname', 'like', $pattern)));
    }
}
