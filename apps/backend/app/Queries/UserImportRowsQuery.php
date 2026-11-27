<?php

namespace App\Queries;

use App\Models\UserImport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserImportRowsQuery extends QueryBuilder
{
    public function __construct(UserImport $import, Request $request)
    {
        parent::__construct($import->rows()->getQuery(), $request);

        $this->allowedFilters(AllowedFilter::exact('status'))
            ->allowedSorts('line')
            ->defaultSort('line');
    }

    public function paginated(int $perPage): LengthAwarePaginator
    {
        return $this->paginate($perPage)->withQueryString();
    }
}
