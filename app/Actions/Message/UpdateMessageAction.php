<?php

namespace App\Actions\Message;

use App\Models\Message;

class UpdateMessageAction
{
    public function execute(Message $message, array $data): Message
    {
        $message->update($data);
        return $message->refresh();
    }
}
