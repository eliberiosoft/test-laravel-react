<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WelcomeResource;
use App\Queries\WelcomeSummaryQuery;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class WelcomeController extends Controller
{
    public function __invoke(Request $request, WelcomeSummaryQuery $summary): WelcomeResource
    {
        $token = $request->user()->currentAccessToken();

        return WelcomeResource::make($summary->for(
            $request->user(),
            $token instanceof PersonalAccessToken ? $token->getKey() : null,
        ));
    }
}
