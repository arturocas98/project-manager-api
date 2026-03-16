<?php

namespace App\Actions\App;

use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class UpdateNotificationReadAction
{
    public function execute(Notification $notification): Notification
    {
        return DB::transaction(function () use ($notification) {
            $notification->read = true;
            $notification->save();
            return $notification;
        });
    }
}
