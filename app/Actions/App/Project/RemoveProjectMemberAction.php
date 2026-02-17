<?php

namespace App\Actions\App\Project;

use App\Models\ProjectUser;
use App\Exceptions\ProjectException;

class RemoveProjectMemberAction
{
    /**
     * Eliminar un miembro del proyecto
     */
    public function execute(int $assignmentId): bool
    {
        try {
            $assignment = ProjectUser::find($assignmentId);

            if (!$assignment) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Asignación no encontrada',
                        'reason' => 'El registro de asignación no existe',
                        'assignment_id' => $assignmentId
                    ]),
                    404
                );
            }

            // Verificar si ya está eliminado
            if ($assignment->trashed()) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Miembro ya eliminado',
                        'reason' => 'Este miembro ya ha sido eliminado del proyecto',
                        'assignment_id' => $assignmentId,
                        'deleted_at' => $assignment->deleted_at?->toDateTimeString()
                    ]),
                    400
                );
            }

            // Soft delete
            $result = $assignment->delete();

            if (!$result) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Error al eliminar',
                        'reason' => 'No se pudo eliminar el miembro del proyecto'
                    ]),
                    500
                );
            }

            return true;

        } catch (ProjectException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Error inesperado',
                    'reason' => 'Error al eliminar miembro: ' . $e->getMessage()
                ]),
                500
            );
        }
    }
}