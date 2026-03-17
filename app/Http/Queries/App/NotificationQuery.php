<?php

namespace App\Http\Queries\App;

use App\Models\Notification;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class NotificationQuery extends QueryBuilder
{
    public function __construct(Request $request)
    {
        parent::__construct(Notification::query(), $request);

        $this
            ->allowedFilters([
                'user_id',
            ]);
    }
}
