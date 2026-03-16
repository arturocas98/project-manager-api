<?php

namespace App\Actions\App;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class CreateNotificationAction
{
    public function execute(array $input): Notification
    {
        return DB::transaction(function () use ($input) {
            $notification = new Notification;
            $notification->fill($input);
            $notification->save();
            return $notification;
        });
    }
}
