<?php

namespace App\Services\Incidence;

use App\Models\Incidence;
use App\Models\TaskComent;
use App\Exceptions\CommentCannotBeDeletedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IncidenceCommentService
{
    public function createComment(string $description, int $incidenceId): TaskComent
    {
        $incidence = Incidence::findOrFail($incidenceId);

        return TaskComent::create([
            'description' => $description,
            'incidence_id' => $incidenceId,
            'created_by' => auth()->id()
        ]);
    }

    public function updateComment(int $commentId, string $description): TaskComent
    {
        $comment = TaskComent::findOrFail($commentId);

        $comment->update([
            'description' => $description
        ]);

        return $comment->fresh();
    }

    public function deleteComment(int $commentId, int $incidenceId): void
    {
        // Verificar que el comentario pertenezca a la incidencia
        $comment = TaskComent::where('id', $commentId)
            ->where('incidence_id', $incidenceId)
            ->with('incidence.incidenceState')
            ->first();

        if (!$comment) {
            throw new ModelNotFoundException('Comment not found or does not belong to this incidence');
        }

        // Verificar si la incidencia está en estado "Finalizada" (state_id = 7)
        if ($comment->incidence && $comment->incidence->incidence_state_id === 7) {
            throw new CommentCannotBeDeletedException(
                'Cannot delete comments from a finalized incidence'
            );
        }

        $comment->delete();
    }
}