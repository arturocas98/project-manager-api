<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Requests\Message\UpdateMessageRequest;
use App\Http\Resources\App\MessageCollection;
use App\Http\Resources\App\MessageResource;
use App\Models\Message;
use App\Services\Message\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService
    ) {
    }

    public function index(int $projectId, Request $request)
    {
        $messages = $this->messageService->getMessagesForProject($projectId, $request);
        return new MessageCollection($messages);
    }

    public function store(StoreMessageRequest $request): JsonResponse
    {
        $message = $this->messageService->createMessage(
            $request->validated(),
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'data' => new MessageResource($message),
            'message' => 'Message created successfully'
        ], 201);
    }

    public function show(int $messageId, Request $request)
    {
        $message = $this->messageService->getMessage($messageId, $request);
        return new MessageResource($message);
    }

    public function update(UpdateMessageRequest $request, Message $message)
    {
        $updatedMessage = $this->messageService->updateMessage($message, $request->validated());
        return new MessageResource($updatedMessage);
    }

    public function destroy(Message $message)
    {
        $this->messageService->deleteMessage($message);
        return response()->json(null, 204);
    }
}
