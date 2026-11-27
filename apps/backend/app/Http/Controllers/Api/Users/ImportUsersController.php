<?php

namespace App\Http\Controllers\Api\Users;

use App\Actions\Users\QueueUserImportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ImportUsersRequest;
use App\Http\Requests\Users\ListImportRowsRequest;
use App\Http\Resources\UserImportResource;
use App\Http\Resources\UserImportRowResource;
use App\Models\UserImport;
use App\Queries\UserImportRowsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ImportUsersController extends Controller
{
    public function store(ImportUsersRequest $request, QueueUserImportAction $queueImport): JsonResponse
    {
        $import = $queueImport->execute($request->file('file'), $request->user());

        return UserImportResource::make($import->refresh())
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED)
            ->header('Location', route('users.imports.show', $import));
    }

    public function show(UserImport $import): UserImportResource
    {
        return UserImportResource::make($import);
    }

    public function rows(ListImportRowsRequest $request, UserImport $import): AnonymousResourceCollection
    {
        return UserImportRowResource::collection(
            (new UserImportRowsQuery($import, $request))->paginated($request->perPage()),
        );
    }
}
