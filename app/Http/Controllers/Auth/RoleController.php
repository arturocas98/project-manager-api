<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SaveRoleAction;
use App\Http\Controllers\Controller;
use App\Http\Queries\Auth\RoleQuery;
use App\Http\Requests\Auth\RoleRequest;
use App\Http\Resources\Auth\RoleResource;
use App\Models\Role;
use App\Utils\Constants;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

#[Group('Auth')]
#[Subgroup('Role')]
#[Authenticated]
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[ResponseFromApiResource(RoleResource::class, Role::class, collection: true)]
    public function index(RoleQuery $query): AnonymousResourceCollection
    {
        return RoleResource::collection($query->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): RoleResource
    {
        return new RoleResource($role);
    }

    /**
     * Store a new resource
     */
    public function store(RoleRequest $request, SaveRoleAction $action): RoleResource
    {
        $role = $action->execute($request->validated());
        return new RoleResource($role);
    }

    /**
     * Update the specified resource
     */
    public function update(RoleRequest $request, SaveRoleAction $action, Role $role): RoleResource
    {
        $action->execute($request->validated(), $role);
        return new RoleResource($role);
    }

    /**
     * Remove the specified resource
     */
    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->exists()) {
            return new JsonResponse(__('validation.app.role.in-use'), Response::HTTP_NOT_ACCEPTABLE);
        }
        $role->delete();
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
