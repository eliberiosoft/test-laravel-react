<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ApiInfoController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name'),
            'version' => config('app.version'),
            'docs' => $this->docsAreVisible() ? ['ui' => url(API_DOCS_UI_PATH), 'openapi' => url(API_DOCS_JSON_PATH)] : null,
        ]);
    }

    private function docsAreVisible(): bool
    {
        return app()->isLocal() || Gate::allows('viewApiDocs');
    }
}
