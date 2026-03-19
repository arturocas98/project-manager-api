<?php
namespace App\Actions\App\Incidence;

use App\Models\TaskComent;
use App\Services\Incidence\IncidenceCommentService;

class CreateCommentAction
{
    public function __construct(
        private readonly IncidenceCommentService $commentService
    ) {}

    public function execute(string $description, int $incidenceId, ?\Illuminate\Http\UploadedFile $file = null): TaskComent
    {
        return $this->commentService->createComment($description, $incidenceId, $file);
    }
}