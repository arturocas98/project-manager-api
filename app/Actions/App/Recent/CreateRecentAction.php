<?php

namespace App\Actions\App\Recent;

use App\Models\Recent;
class CreateRecentAction
{
    public function execute(array $data): ?Recent
    {
        try {
            // Verificar si ya existe un Recent con el mismo título para este usuario
            $existingRecent = Recent::where('user_id', auth()->id())
                ->where('title', $data['title'])
                ->first();

            // Si ya existe, retornar null o el existente (sin lanzar excepción)
            if ($existingRecent) {
                return null; // o return $existingRecent si prefieres retornar el existente
            }

            $recent = Recent::create([
                'title' => $data['title'],
                'user_id' => auth()->id(),
                'link' => $data['link'],
                'icon' => $data['icon'],
            ]);

            if (!$recent) {
                throw new \Exception('No se pudo crear el registro recent');
            }

            return $recent;

        } catch (\Exception $e) {
            throw new \Exception('Error al crear el registro recent: ' . $e->getMessage());
        }
    }
}