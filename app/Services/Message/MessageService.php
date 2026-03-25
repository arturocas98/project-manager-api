<?php

namespace App\Services\Message;

use App\Actions\Message\CreateMessageAction;
use App\Actions\Message\UpdateMessageAction;
use App\Actions\Message\DeleteMessageAction;
use App\Http\Queries\App\MessageQuery;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class MessageService
{
    public function __construct(
        private CreateMessageAction $createAction,
        private UpdateMessageAction $updateAction,
        private DeleteMessageAction $deleteAction
    ) {
    }

    public function getMessagesForProject(int $projectId, Request $request)
    {
        $query = new MessageQuery($request);
        return $query->forProject($projectId)->paginate();
    }

    public function getMessage(int $id, Request $request): ?Message
    {
        $query = new MessageQuery($request);
        return $query->find($id);
    }

    public function createMessage(array $data, ?UploadedFile $file = null): Message
    {
        return $this->createAction->execute($data, $file);
    }

    public function updateMessage(Message $message, array $data): Message
    {
        return $this->updateAction->execute($message, $data);
    }

    public function deleteMessage(Message $message): bool
    {
        return $this->deleteAction->execute($message);
    }
}
