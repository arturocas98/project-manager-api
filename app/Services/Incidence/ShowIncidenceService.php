<?php

namespace App\Services\Incidence;


use App\Exceptions\IncidenceException;
use App\Models\Incidence;
use App\Models\Project;

class ShowIncidenceService
{
    /**
     * Obtener una incidencia con todos sus hijos en orden cronológico
     */
    public function getIncidenceWithChildren(int $projectId, int $incidenceId): Incidence
    {
        // Validar acceso al proyecto (puedes mover esta lógica al service si quieres)
        $this->validateProjectAccess($projectId);

        // Obtener la incidencia principal
        $incidence = Incidence::where('project_id', $projectId)
            ->where('id', $incidenceId)
            ->with([
                'incidencePriority',
                'incidenceType',
                'incidenceState',
                'createdBy',
                'assignedUser',
                'parentIncidence',
                'project'
            ])
            ->firstOrFail();

        // Cargar los hijos de forma recursiva y ordenados
        $incidence->children = $this->getChildrenRecursively($incidenceId, $projectId);

        return $incidence;
    }

    /**
     * Validar que el usuario tiene acceso al proyecto
     */
    private function validateProjectAccess(int $projectId): void
    {
        $user = auth()->user();
        $project = Project::findOrFail($projectId);

        // Usando el método que ya tienes en el User model
        if (!$user->hasProjectAccess($projectId)) {
            throw new IncidenceException(
                json_encode([
                    'error' => 'Acceso denegado',
                    'reason' => 'No tienes acceso a este proyecto',
                    'project_id' => $projectId,
                    'user_id' => $user->id,
                ]),
                403
            );
        }
    }

    /**
     * Obtener hijos recursivamente con ordenamiento cronológico
     */
    private function getChildrenRecursively(int $parentId, int $projectId): array
    {
        // Obtener hijos directos ordenados
        $children = Incidence::where('parent_incidence_id', $parentId)
            ->where('project_id', $projectId)
            ->with([
                'incidencePriority',
                'incidenceType',
                'incidenceState',
                'createdBy',
                'assignedUser',
            ])
            ->get()
            ->map(function ($child) use ($projectId) {
                // Convertir a array y agregar hijos recursivamente
                $childArray = $child->toArray();

                // Ordenamiento: start_date si existe, sino created_at
                $childArray['sort_date'] = $child->start_date ?? $child->created_at;

                // Obtener hijos de este hijo recursivamente
                $childArray['children'] = $this->getChildrenRecursively($child->id, $projectId);

                return $childArray;
            })
            ->sortBy('sort_date') // Ordenar por fecha
            ->values() // Reindexar después de ordenar
            ->toArray();

        return $children;
    }

    /**
     * Método alternativo más eficiente (una sola consulta)
     */
    public function getIncidenceWithChildrenOptimized(int $projectId, int $incidenceId): Incidence
    {
        // Validar acceso al proyecto
        $this->validateProjectAccess($projectId);

        // Obtener la incidencia principal
        $incidence = Incidence::where('project_id', $projectId)
            ->where('id', $incidenceId)
            ->with([
                'incidencePriority',
                'incidenceType',
                'incidenceState',
                'createdBy',
                'assignedUser',
                'parentIncidence',
                'project'
            ])
            ->firstOrFail();

        // Obtener TODAS las incidencias del proyecto en una sola consulta
        $allIncidences = Incidence::where('project_id', $projectId)
            ->with([
                'incidencePriority',
                'incidenceType',
                'incidenceState',
                'createdBy',
                'assignedUser',
            ])
            ->get()
            ->keyBy('id');

        // Construir el árbol
        $incidence->children = $this->buildTree($incidenceId, $allIncidences);

        return $incidence;
    }

    /**
     * Construir árbol de incidencias
     */
    private function buildTree(int $parentId, $allIncidences): array
    {
        $children = [];

        foreach ($allIncidences as $id => $incidence) {
            if ($incidence->parent_incidence_id == $parentId) {
                $incidenceArray = $incidence->toArray();
                $incidenceArray['sort_date'] = $incidence->start_date ?? $incidence->created_at;
                $incidenceArray['children'] = $this->buildTree($incidence->id, $allIncidences);
                $children[] = $incidenceArray;
            }
        }

        // Ordenar por fecha
        usort($children, function($a, $b) {
            return strtotime($a['sort_date']) <=> strtotime($b['sort_date']);
        });

        return $children;
    }
}