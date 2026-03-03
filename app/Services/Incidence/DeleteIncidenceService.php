<?php

namespace App\Services\Incidence;


use App\Exceptions\IncidenceException;
use App\Models\Incidence;
use Illuminate\Support\Facades\DB;

class DeleteIncidenceService
{
    /**
     * Eliminar una incidencia y todas sus incidencias hijas
     */
    public function delete(int $incidenceId): array
    {

        $incidence = Incidence::with(['project', 'incidenceType', 'incidenceState', 'incidencePriority'])
            ->find($incidenceId);

        if (!$incidence) {
            throw new IncidenceException(
                json_encode([
                    'error' => 'Incidencia no encontrada',
                    'reason' => 'La incidencia que intentas eliminar no existe',
                    'incidence_id' => $incidenceId,
                ]),
                404
            );
        }


        return DB::transaction(function () use ($incidence) {

            $mainIncidenceData = [
                'id' => $incidence->id,
                'title' => $incidence->title,
                'project_id' => $incidence->project_id,
                'project_name' => $incidence->project?->name,
                'type' => $incidence->incidenceType?->name,
                'state' => $incidence->incidenceState?->name,
                'priority' => $incidence->incidencePriority?->name,
                'created_by' => $incidence->createdBy?->name,
                'assigned_to' => $incidence->assignedUser?->name,
                'due_date' => $incidence->due_date?->toDateTimeString(),
                'start_date' => $incidence->start_date?->toDateTimeString(),
            ];

            $allChildIncidences = $this->getAllChildIncidences($incidence->id);


            $childIncidencesData = collect($allChildIncidences)->map(function($childId) {
                $child = Incidence::with(['incidenceType', 'incidenceState', 'incidencePriority'])
                    ->find($childId);

                return $child ? [
                    'id' => $child->id,
                    'title' => $child->title,
                    'type' => $child->incidenceType?->name,
                    'state' => $child->incidenceState?->name,
                    'priority' => $child->incidencePriority?->name,
                ] : null;
            })->filter()->values()->toArray();


            if (!empty($allChildIncidences)) {
                Incidence::whereIn('id', $allChildIncidences)->delete();
            }


            $incidence->delete();

            return [
                'message' => 'Incidencia eliminada correctamente',
                'deleted_incidence' => $mainIncidenceData,
                'child_incidences' => [
                    'count' => count($allChildIncidences),
                    'items' => $childIncidencesData,
                ],
                'total_deleted' => count($allChildIncidences) + 1,
                'timestamp' => now()->toDateTimeString(),
            ];
        });
    }

    /**
     * Obtener todas las incidencias hijas de forma recursiva
     */
    private function getAllChildIncidences(int $parentId): array
    {
        $childIds = [];


        $directChildren = Incidence::where('parent_incidence_id', $parentId)
            ->pluck('id')
            ->toArray();

        foreach ($directChildren as $childId) {
            $childIds[] = $childId;

            $grandChildren = $this->getAllChildIncidences($childId);
            $childIds = array_merge($childIds, $grandChildren);
        }

        return $childIds;
    }

    /**
     * Validar permisos para eliminar la incidencia
     * (Personaliza esta lógica según tus necesidades)
     */
}