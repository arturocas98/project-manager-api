<?php

namespace App\Actions\App\Project;

use App\Exceptions\ProjectException;
use App\Models\Project;

class DeleteProjectAction
{
    public function execute(Project $project): bool
    {
        try {
            // Verificar que el proyecto existe y no está ya eliminado
            if ($project->trashed()) {
                throw new ProjectException('El proyecto ya está eliminado', 400);
            }

            // Soft delete
            $result = $project->delete();

            if (! $result) {
                throw new ProjectException('No se pudo eliminar el proyecto', 500);
            }

            return true;

        } catch (ProjectException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ProjectException(
                'Error al eliminar el proyecto: '.$e->getMessage(),
                500
            );
        }
    }
}
