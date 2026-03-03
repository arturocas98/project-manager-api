<?php

namespace App\Services\Project;

use App\Actions\App\Project\AssignUserToRoleAction;
use App\Exceptions\ProjectException;
use App\Models\Project;
use App\Models\ProjectPermissionScheme;
use App\Models\ProjectRole;
use App\Models\ProjectRolePermission;
use App\Models\ProjectUser;
use Illuminate\Support\Facades\DB;

class ProjectMemberService
{
    /**
     * Mapeo de tipos de rol a IDs de esquemas de permisos
     */
    protected array $roleSchemeMap = [
        'administrators' => 1,      // Esquema con TODOS los permisos
        'project manager' => 2,      // Esquema con permisos de gestión
        'developer' => 3,            // Esquema con permisos de desarrollo
        'tester' => 4,               // Esquema con permisos de testing
        'team member' => 5,          // Esquema con permisos de miembro de equipo
        'client' => 6,               // Esquema con permisos de cliente (solo ver)
        'guest' => 7,                // Esquema con permisos de invitado (muy limitado)
        'supervisor' => 8,           // Esquema con permisos de supervisión
        'owner' => 1,                // Dueño = mismo que admin
        'external contributor' => 9,  // Esquema limitado para externos
    ];

    public function __construct(
        private AssignUserToRoleAction $assignUserToRole,
    ) {}

    public function addMember(Project $project, int $userId, string $roleType): ProjectUser
    {
        // VALIDACIONES
        $this->validateProject($project);
        $this->validateAdminPermissions($project);
        $this->validateUserNotInProject($project, $userId);

        // Obtener o crear el rol
        $projectRole = $this->getProjectRole($project, $roleType);

        // TRANSACCIÓN - Asignar usuario y luego asignar permisos al rol si es necesario
        return DB::transaction(function () use ($projectRole, $userId, $roleType) {

            // PASO 1: Asignar usuario al rol (usa el Action existente)
            $assignment = $this->assignUserToRole->execute($projectRole->id, $userId);

            // 🔥 PASO 2: ASIGNAR ESQUEMA DE PERMISOS AL ROL (si no existe)
            $this->assignPermissionSchemeToRole($projectRole, $roleType);

            // Cargar relaciones para la respuesta
            return $assignment->load(['role', 'role.permissionScheme.scheme', 'user']);
        });
    }
    private function assignPermissionSchemeToRole(ProjectRole $role, string $roleType): void
    {
        // Verificar si el rol ya tiene un esquema asignado
        $existingPermission = ProjectRolePermission::where('project_role_id', $role->id)->first();

        if ($existingPermission) {
            return;
        }

        // Obtener el ID del esquema según el tipo de rol
        $schemeId = $this->getSchemeIdForRoleType($roleType);

        if (!$schemeId) {
            // Buscar un esquema por defecto o lanzar excepción
            $schemeId = $this->getDefaultSchemeId();

            if (!$schemeId) {
                throw new ProjectException(
                    json_encode([
                        'error' => 'Configuración incompleta',
                        'reason' => 'No hay esquema de permisos definido para este rol',
                        'role_type' => $roleType
                    ]),
                    500
                );
            }
        }

        // Crear el registro en project_role_permissions
        $permissionAssignment = ProjectRolePermission::create([
            'project_role_id' => $role->id,
            'permission_scheme_id' => $schemeId,
        ]);
    }

    /**
     * Obtiene el ID del esquema según el tipo de rol
     */
    private function getSchemeIdForRoleType(string $roleType): ?int
    {
        // Opción 1: Usar el mapeo definido en código
        if (isset($this->roleSchemeMap[$roleType])) {
            return $this->roleSchemeMap[$roleType];
        }

        // Opción 2: Buscar en base de datos (más flexible)
        $scheme = ProjectPermissionScheme::where('default_for_role', $roleType)->first();

        if ($scheme) {
            return $scheme->id;
        }

        return null;
    }

    /**
     * Obtiene un esquema por defecto (primer esquema activo)
     */
    private function getDefaultSchemeId(): ?int
    {
        return ProjectPermissionScheme::orderBy('id')->value('id');
    }

    /**
     * ✅ Obtener el rol específico del proyecto por su tipo
     * Si no existe, lo crea automáticamente
     */
    private function getProjectRole(Project $project, string $roleType): ProjectRole
    {
        // Buscar si existe el rol en el proyecto
        $role = $project->roles()
            ->where('type', $roleType)
            ->first();

        // Si no existe, lo creamos
        if (! $role) {
            $role = ProjectRole::create([
                'project_id' => $project->id,
                'type' => $roleType,
            ]);
        }

        return $role;
    }

    /**
     * Validar que el proyecto no esté eliminado
     */
    private function validateProject(Project $project): void
    {
        if ($project->trashed()) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Proyecto no disponible',
                    'reason' => 'No se pueden añadir miembros a un proyecto eliminado',
                    'project_id' => $project->id,
                    'deleted_at' => $project->deleted_at?->toDateTimeString(),
                ]),
                400
            );
        }
    }

    /**
     * Validar que el usuario autenticado sea administrador
     */
    private function validateAdminPermissions(Project $project): void
    {
        $userId = auth()->id();

        if (! $userId) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Usuario no autenticado',
                    'reason' => 'Se requiere un usuario autenticado para esta acción',
                ]),
                401
            );
        }

        $isAdmin = $project->roles()
            ->where('type', 'administrators')
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->exists();

        if (! $isAdmin) {
            $userRole = $project->roles()
                ->whereHas('users', fn($q) => $q->where('user_id', $userId))
                ->first();

            $roleName = $userRole?->type ?? 'Sin rol asignado';

            throw new ProjectException(
                json_encode([
                    'error' => 'Permiso denegado',
                    'reason' => 'Solo los administradores pueden añadir miembros',
                    'user_id' => $userId,
                    'user_role' => $roleName,
                    'project_id' => $project->id,
                    'required_role' => 'administrators'
                ]),
                403
            );
        }
    }

    /**
     * Validar que el usuario NO tiene NINGÚN rol en el proyecto
     */
    private function validateUserNotInProject(Project $project, int $userId): void
    {
        // Buscar si el usuario tiene ALGÚN project_role asociado a este proyecto
        $existingAssignment = ProjectUser::whereHas('role', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })
            ->where('user_id', $userId)
            ->with('role')
            ->first();

        if ($existingAssignment) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Usuario ya en el proyecto',
                    'reason' => 'El usuario ya es miembro del proyecto',
                    'user_id' => $userId,
                    'project_id' => $project->id,
                    'current_role' => [
                        'id' => $existingAssignment->project_role_id,
                        'type' => $existingAssignment->role->type,
                        'assignment_id' => $existingAssignment->id,
                    ],
                    'suggestion' => 'Solo se permite un rol por usuario en el proyecto',
                ]),
                400
            );
        }
    }
}