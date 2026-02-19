<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SaveUserAction;
use App\Http\Controllers\Controller;
use App\Http\Queries\Auth\UserQuery;
use App\Http\Requests\Auth\UserRequest;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Illuminate\Http\Response;

#[Group('Auth')]
#[Subgroup('User')]
#[Authenticated]
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserQuery $query): AnonymousResourceCollection
    {
        return UserResource::collection($query->paginate());
    }

    /**
     * Display the specified resource.
     */
    public function show($id): UserResource
    {
        $user = User::withTrashed()->findOrFail($id);
        return new UserResource($user);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request, SaveUserAction $action): UserResource
    {
        $user = $action->execute($request->safe()->collect());
        return new UserResource($user->refresh());
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(
        UserRequest $request,
        SaveUserAction $action,
        $id
    ): UserResource|JsonResponse {
        $user = User::withTrashed()->findOrFail($id);
        $user = $action->execute($request->safe()->collect(), $user);
        if (!$user->deleted_at) return new UserResource($user);
        else return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
