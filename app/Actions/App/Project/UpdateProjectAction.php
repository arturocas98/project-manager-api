<?php

namespace App\Actions\App\Project;

use App\Models\Project;

class UpdateProjectAction
{
    /**
     * Actualizar un proyecto
     *
     * @throws \Exception
     */
    public function execute(Project $project, array $data): Project
    {
        try {
            // Solo actualizar los campos permitidos
            $fillable = array_intersect_key($data, array_flip([
                'name',
                'key',
                'description',
            ]));

            // Si no hay nada para actualizar
            if (empty($fillable)) {
                throw new \Exception('No hay datos para actualizar');
            }

            // Actualizar el proyecto
            $project->update($fillable);

            // Refrescar para obtener datos actualizados
            $project->refresh();

            return $project;

        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar el proyecto: '.$e->getMessage());
        }
    }
}
