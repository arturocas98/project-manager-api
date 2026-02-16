<?php


namespace App\Services;

use App\Actions\App\Project\DeleteProjectAction;
use App\Models\Project;
use App\Exceptions\ProjectException;
use Illuminate\Support\Facades\DB;

class ProjectDeleteService
{
    public function __construct(
        private DeleteProjectAction $deleteProject
    ) {}

    /**
     * Eliminar proyecto con verificación de permisos
     */
    public function delete(Project $project): bool
    {
        // VALIDACIONES DE NEGOCIO
        $this->validateProject($project);
        $this->validatePermissions($project);
        $this->validateNotLastAdmin($project);
        $this->validateNoCriticalIssues($project); // Opcional

        // TRANSACCIÓN
        return DB::transaction(function () use ($project) {

            // Limpieza previa (opcional)
            $this->cleanupRelations($project);

            // EJECUTAR ACCIÓN
            $result = $this->deleteProject->execute($project);

            // VERIFICAR RESULTADO
            if (!$result) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Error al eliminar el proyecto',
                        'reason' => 'La acción de eliminación falló',
                        'project_id' => $project->id
                    ]),
                    500
                );
            }

            return true;
        });
    }

    /**
     * Validar que el proyecto existe y no está eliminado
     */
    private function validateProject(Project $project): void
    {
        if ($project->trashed()) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Proyecto ya eliminado',
                    'reason' => 'El proyecto ya ha sido eliminado anteriormente',
                    'project_id' => $project->id,
                    'deleted_at' => $project->deleted_at?->toDateTimeString()
                ]),
                400
            );
        }
    }

    /**
     * Validar permisos de administrador
     */
    private function validatePermissions(Project $project): void
    {
        $userId = auth()->id();

        if (!$userId) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Usuario no autenticado',
                    'reason' => 'Se requiere un usuario autenticado para esta acción'
                ]),
                401
            );
        }

        $isAdmin = $project->roles()
            ->where('type', 'Administrators')
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();

        if (!$isAdmin) {
            $userRole = $project->roles()
                ->whereHas('users', fn($q) => $q->where('user_id', $userId))
                ->first();

            $roleName = $userRole?->type ?? 'Sin rol asignado';

            throw new ProjectException(
                json_encode([
                    'error' => 'Permiso denegado',
                    'reason' => 'Se requiere rol de Administrador para eliminar proyectos',
                    'user_id' => $userId,
                    'user_role' => $roleName,
                    'project_id' => $project->id,
                    'required_role' => 'Administrators'
                ]),
                403
            );
        }
    }

    /**
     * Validar que no sea el último administrador
     */
    private function validateNotLastAdmin(Project $project): void
    {
        $adminCount = $project->roles()
            ->where('type', 'Administrators')
            ->withCount('users')
            ->get()
            ->sum('users_count');

        if ($adminCount === 1) {
            // Verificar que el usuario actual es ese único admin
            $userId = auth()->id();
            $isTheOnlyAdmin = $project->roles()
                ->where('type', 'Administrators')
                ->whereHas('users', fn($q) => $q->where('user_id', $userId))
                ->exists();

            if ($isTheOnlyAdmin) {
                // Buscar otros admins potenciales (otros roles con capacidad de ser admin)
                $otherAdmins = $project->roles()
                    ->where('type', '!=', 'Administrators')
                    ->with('users')
                    ->get()
                    ->map(fn($role) => $role->users)
                    ->flatten()
                    ->unique('id')
                    ->values();

                throw new ProjectException(
                    json_encode([
                        'error' => 'Último administrador',
                        'reason' => 'No puedes eliminar el proyecto siendo el único administrador',
                        'project_id' => $project->id,
                        'project_name' => $project->name,
                        'admin_count' => $adminCount,
                        'suggestion' => 'Antes de eliminar, promueve a otro usuario a Administrador',
                        'candidates' => $otherAdmins->map(fn($user) => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'current_role' => $this->getUserRoleInProject($project, $user->id)
                        ])
                    ]),
                    400
                );
            }
        }
    }

    /**
     * Validar que no haya issues críticos (opcional)
     */
    private function validateNoCriticalIssues(Project $project): void
    {
        // Ejemplo: Verificar si hay issues abiertos
        if (method_exists($project, 'issues') && $project->issues()->where('status', 'open')->exists()) {
            $openIssues = $project->issues()->where('status', 'open')->count();

            throw new ProjectException(
                json_encode([
                    'error' => 'Proyecto con issues pendientes',
                    'reason' => 'No puedes eliminar un proyecto que tiene issues abiertos',
                    'project_id' => $project->id,
                    'open_issues_count' => $openIssues,
                    'suggestion' => 'Resuelve o reasigna los issues abiertos antes de eliminar'
                ]),
                400
            );
        }
    }

    /**
     * Limpiar relaciones antes de eliminar
     */
    private function cleanupRelations(Project $project): void
    {
        // Ejemplo: Desasignar issues (si los tuvieras)
        // $project->issues()->update(['project_id' => null]);

        // Ejemplo: Archivar logs
        // $project->activityLogs()->update(['archived' => true]);

        // Por ahora, no hacemos nada adicional porque CASCADE ya elimina
    }

    /**
     * Obtener rol de un usuario en el proyecto
     */
    private function getUserRoleInProject(Project $project, int $userId): ?string
    {
        $role = $project->roles()
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->first();

        return $role?->type;
    }
}