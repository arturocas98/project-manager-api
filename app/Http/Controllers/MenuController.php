<?php

namespace App\Http\Controllers;

use App\Actions\App\Menu\SaveMenuAction;
use App\Http\Controllers\Controller;
use App\Http\Queries\App\MenuQuery;
use App\Http\Requests\App\MenuRequest;
use App\Http\Resources\App\MenuResource;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('App')]
#[Subgroup('Menu')]
#[Authenticated]
class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MenuQuery $query): AnonymousResourceCollection
    {
        return MenuResource::collection($query->paginate());
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu): MenuResource
    {
        return new MenuResource($menu);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MenuRequest $request, SaveMenuAction $action): MenuResource
    {
        $menu = $action->execute($request->validated());
        return new MenuResource($menu);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MenuRequest $request, SaveMenuAction $action, Menu $menu): MenuResource
    {
        $action->execute($request->validated(), $menu);

        return new MenuResource($menu);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
