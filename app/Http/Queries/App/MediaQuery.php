<?php

namespace App\Http\Queries\App;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Media::query());

        $this->allowedFilters([
            'model_type',
            'model_id',
            'collection_name',
            'name',
            'file_name',
            'mime_type'
        ])
             ->allowedSorts([
                 'id',
                 'size',
                 'created_at',
             ]);
    }
}
