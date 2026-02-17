<?php

namespace App\Actions\App\Project;

use App\Models\ProjectUser;
use App\Exceptions\ProjectException;
use Illuminate\Support\Facades\DB;

class UpdateMemberRoleAction
{
    /**
     * Cambiar el rol de un miembro del proyecto
     */
    public function execute(int $assignmentId, int $newRoleId): ProjectUser
    {
        try {
            return DB::transaction(function () use ($assignmentId, $newRoleId) {

                $assignment = ProjectUser::where('id', $assignmentId)
                    ->lockForUpdate()
                    ->first();

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

                // Guardar el rol anterior para referencia
                $oldRoleId = $assignment->project_role_id;

                // Actualizar al nuevo rol
                $assignment->project_role_id = $newRoleId;
                $assignment->save();

                // Recargar con relaciones
                $assignment->load(['role', 'user']);

                return $assignment;

            });
        } catch (ProjectException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Error inesperado',
                    'reason' => 'Error al cambiar el rol: ' . $e->getMessage()
                ]),
                500
            );
        }
    }
}