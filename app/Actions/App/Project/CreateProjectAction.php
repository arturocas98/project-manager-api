<?php

namespace App\Actions\App\Project;

use App\Models\Project;
use Illuminate\Support\Str;

class CreateProjectAction
{
    public function execute(array $data): Project
    {
        try {
            $project = Project::create([
                'name' => $data['name'],
                'key' => $this->generateKey($data['name']),
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if (! $project) {
                throw new \Exception('No se pudo crear el proyecto');
            }

            return $project;

        } catch (\Exception $e) {
            throw new \Exception('Error al crear proyecto: '.$e->getMessage());
        }
    }

    private function generateKey(string $name): string
    {
        // Tomar primeras 3 letras del nombre
        $baseKey = strtoupper(Str::substr($name, 0, 3));

        // Verificar si ya existe
        $originalKey = $baseKey;
        $counter = 1;

        while (Project::withTrashed()->where('key', $baseKey)->exists()) {
            $baseKey = $originalKey.$counter;
            $counter++;
        }

        return $baseKey;
    }
}
