<?php

namespace App\Actions\App\Project;

use App\Exceptions\ProjectException;
use App\Models\ProjectRole;
use App\Models\ProjectUser;

class AssignUserToRoleAction
{
    public function execute(int $projectRoleId, int $userId): ProjectUser
    {
        try {
            // Verificar que el usuario no tenga YA este rol específico
            $exists = ProjectUser::where('project_role_id', $projectRoleId)
                ->where('user_id', $userId)
                ->exists();

            if ($exists) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Usuario ya asignado',
                        'reason' => 'El usuario ya tiene este rol en el proyecto',
                        'user_id' => $userId,
                        'project_role_id' => $projectRoleId
                    ]),
                    400
                );
            }

            // Crear la asignación
            $assignment = ProjectUser::create([
                'project_role_id' => $projectRoleId,
                'user_id' => $userId
            ]);

            return $assignment;

        } catch (ProjectException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Error inesperado',
                    'reason' => 'Error al asignar usuario al rol: ' . $e->getMessage()
                ]),
                500
            );
        }
    }
}