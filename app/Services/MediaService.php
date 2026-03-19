<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public function store(array $data): Media
    {
        $modelClass = Relation::getMorphedModel($data['model_type']) ?? $data['model_type'];

        if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
            abort(400, 'Invalid model type provided.');
        }

        $model = $modelClass::findOrFail($data['model_id']);

        $collectionName = $data['collection_name'] ?? 'default';

        return $model->addMedia($data['file'])
                     ->toMediaCollection($collectionName);
    }

    public function destroy(Media $media): void
    {
        $media->delete();
    }
}
