<?php

namespace App\Actions\Message;

use App\Models\Message;

class DeleteMessageAction
{
    public function execute(Message $message): bool
    {
        return $message->delete();
    }
}
