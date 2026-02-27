<?php

namespace App\Actions\App\Recent;

use App\Models\Recent;

class RemoveRecenteAction
{
    public function execute(Recent $recent): bool
    {
        try {
            // Verificar que el proyecto existe y no está ya eliminado
            if ($recent->trashed()) {
                throw new \Exception('El proyecto ya está eliminado', 400);
            }

            // Soft delete
            $result = $recent->delete();

            if (! $result) {
                throw new \Exception('No se pudo eliminar el proyecto', 500);
            }

            return true;

        } catch (\Exception $e) {
            throw new \Exception(
                'Error al eliminar el proyecto: '.$e->getMessage(),
                500
            );
        }
    }
}
