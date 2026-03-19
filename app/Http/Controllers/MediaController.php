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
        $this->mediaService->destroy($media);

        return response()->json(null, 204);
    }
}
