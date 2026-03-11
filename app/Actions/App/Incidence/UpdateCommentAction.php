<?php
namespace App\Actions\App\Incidence;

use App\Models\TaskComent;
use App\Services\Incidence\IncidenceCommentService;

class UpdateCommentAction
{
    public function __construct(
        private readonly IncidenceCommentService $commentService
    ) {}

    public function execute(int $commentId, string $description): TaskComent
    {
        return $this->commentService->updateComment($commentId, $description);
    }
}