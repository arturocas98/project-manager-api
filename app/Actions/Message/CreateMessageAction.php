<?php

namespace App\Actions\Message;

use App\Models\Message;
use Illuminate\Http\UploadedFile;

class CreateMessageAction
{
    public function execute(array $data, ?UploadedFile $file = null): Message
    {
        unset($data['file']);
        $message = Message::create($data);
        if ($file) {
            $message->addMedia($file)->toMediaCollection('documents');
        }

        return $message;
    }
}
