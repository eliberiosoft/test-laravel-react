<?php

namespace App\Http\Controllers\Api;

use App\Actions\Menu\GetUserMenuAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\MenuItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    public function __invoke(Request $request, GetUserMenuAction $menu): AnonymousResourceCollection
    {
        return MenuItemResource::collection($menu->execute($request->user()));
    }
}
