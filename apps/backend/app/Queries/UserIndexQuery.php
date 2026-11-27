<?php

namespace App\Queries;

use App\Models\User;
use App\Queries\Filters\UserSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserIndexQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        parent::__construct(User::query()->with(['person', 'roles']), $request);

        $this->allowedFilters(
            AllowedFilter::exact('status'),
            AllowedFilter::callback('role', fn (Builder $query, mixed $role) => $query->role($role)),
            AllowedFilter::custom('search', new UserSearchFilter)->delimiter(''),
        )
            ->allowedSorts('username', 'email', 'status', 'created_at')
            ->defaultSort('-created_at');
    }

    public function paginated(int $perPage): LengthAwarePaginator
    {
        return $this->paginate($perPage)->withQueryString();
    }
}
