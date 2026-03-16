<?php

namespace App\Http\Controllers;

use App\Actions\App\CreateNotificationAction;
use App\Actions\App\UpdateNotificationReadAction;
use App\Http\Queries\App\NotificationQuery;
use App\Http\Requests\App\NotificationCreateRequest;
use App\Http\Resources\App\NotificationResource;
use App\Http\Resources\App\NotificationUserResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;


#[Group('Central')]
#[Subgroup('Notification')]
#[Authenticated]
class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[ResponseFromApiResource(NotificationResource::class, Notification::class, collection: true)]
    public function index(NotificationQuery $query): AnonymousResourceCollection
    {
        return NotificationResource::collection($query->paginate());
    }

    public function notifications(NotificationQuery $query): AnonymousResourceCollection
    {
        $query->where('user_id', auth()->id());

        return NotificationResource::collection($query->paginate());
    }
    /**
     * Display the specified resource.
     */
    public function show(Notification $notification): NotificationResource
    {
        return new NotificationResource($notification);
    }

    public function user(User $user): NotificationUserResource
    {
        return new NotificationUserResource($user);
    }

    public function store(NotificationCreateRequest $request, CreateNotificationAction $action): NotificationResource
    {
        $notification = $action->execute($request->validated());
        return new NotificationResource($notification);
    }

    public function read(UpdateNotificationReadAction $action, Notification $notification): NotificationResource
    {
        $action->execute($notification);
        return new NotificationResource($notification);
    }

}
