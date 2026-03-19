<?php

namespace App\Actions\App\Incidence;

use App\Services\Incidence\IncidenceCommentService;

class DeleteCommentAction
{
    public function __construct(
        private readonly IncidenceCommentService $commentService
    ) {}

    public function execute(int $commentId, int $incidenceId, int $projectId): void
    {
        $this->commentService->deleteComment($commentId, $incidenceId, $projectId);
    }
}