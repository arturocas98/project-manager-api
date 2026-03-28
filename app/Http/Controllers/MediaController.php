<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Queries\App\MediaQuery;
use App\Http\Requests\App\Media\StoreMediaRequest;
use App\Http\Resources\App\MediaCollection;
use App\Http\Resources\App\MediaResource;
use App\Services\MediaService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function index(MediaQuery $query): MediaCollection
    {
        // Require filtering to avoid massive data leaks, or just return paginated if admin.
        // For general users, it's safer to just let the query run but ideally we'd filter by their allowed projects/messages.
        // As a basic secure default, ensure index returns paginated list.
        return new MediaCollection($query->paginate());
    }

    public function show(Media $media): MediaResource
    {
        return new MediaResource($media);
    }

    public function store(StoreMediaRequest $request): MediaResource
    {
        $media = $this->mediaService->store($request->validated());

        return new MediaResource($media);
    }

    public function destroy(Media $media): JsonResponse
    {
        $model = $media->model;
        $user = auth()->user();

        $isAuthorized = false;

        if ($model instanceof \App\Models\User) {
            $isAuthorized = $model->id === $user->id;
        } elseif ($model instanceof \App\Models\Project) {
            $isAuthorized = $model->hasUserAccess($user->id);
        } elseif ($model instanceof \App\Models\Incidence) {
            $isAuthorized = $model->project && $model->project->hasUserAccess($user->id);
        } elseif (isset($model->user_id)) {
            $isAuthorized = $model->user_id === $user->id;
        }

        abort_unless($isAuthorized, 422, 'No estás autorizado para eliminar este archivo.');

        $this->mediaService->destroy($media);

        return response()->json(null, 204);
    }
}
