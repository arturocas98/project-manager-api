<?php

namespace App\Actions\App\Project;

use App\Models\ProjectRole;
use App\Models\ProjectUser;
use App\Exceptions\ProjectException;

class RemoveProjectMemberAction
{
    public function execute(int $projectRoleId): bool
    {
        try {

            $role = ProjectRole::find($projectRoleId);

            if (!$role) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Rol no encontrado',
                        'reason' => 'El rol del proyecto no existe',
                        'project_role_id' => $projectRoleId
                    ]),
                    404
                );
            }

            // Elimina el rol (esto eliminará automáticamente project_users si hay cascade)
            $result = $role->delete();

            if (!$result) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Error al eliminar',
                        'reason' => 'No se pudo eliminar el rol del proyecto'
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
                    'reason' => 'Error al eliminar rol: ' . $e->getMessage()
                ]),
                500
            );
        }
    }
}
