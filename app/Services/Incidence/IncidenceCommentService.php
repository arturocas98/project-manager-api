<?php

namespace App\Services\Incidence;

use App\Models\Incidence;
use App\Models\TaskComent;
use App\Exceptions\CommentCannotBeDeletedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IncidenceCommentService
{
    public function createComment(string $description, int $incidenceId, ?\Illuminate\Http\UploadedFile $file = null): TaskComent
    {
        $incidence = Incidence::findOrFail($incidenceId);

        $comment = TaskComent::create([
            'description' => $description,
            'incidence_id' => $incidenceId,
            'created_by' => auth()->id()
        ]);

        if ($file) {
            $media = $comment->addMedia($file)->toMediaCollection('documents');
            // Cambiar el model_id para guardar el ID de la incidencia en lugar del ID del comentario
            $media->model_id = $incidenceId;
            $media->save();
        }

        return $comment;
    }

    public function updateComment(int $commentId, string $description): TaskComent
    {
        $comment = TaskComent::findOrFail($commentId);

        $comment->update([
            'description' => $description
        ]);

        return $comment->fresh();
    }

    public function deleteComment(int $commentId, int $incidenceId, int $projectId): void
    {
        // Verificar que el comentario pertenezca a la incidencia
        $comment = TaskComent::where('id', $commentId)
            ->where('incidence_id', $incidenceId)
            ->with('incidence.incidenceState')
            ->first();

        if (!$comment) {
            throw new ModelNotFoundException('Comment not found or does not belong to this incidence');
        }

        // Permisos para eliminar el comentario
        $user = auth()->user();
        if ($user) {
            $role = $user->getProjectRole($projectId);
            $isCreator = $comment->created_by === $user->id;
            $isAuthorizedRole = $role && in_array($role->code, ['ADM', 'LDR']);
            
            if (!$isCreator && !$isAuthorizedRole) {
                throw new \App\Exceptions\IncidenceException('No tienes permisos para eliminar este comentario', 422);
            }
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