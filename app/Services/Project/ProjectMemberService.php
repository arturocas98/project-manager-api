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
     * Mapeo de códigos de rol a nombres (para crear el rol)
     */
    protected array $roleNameMap = [
        'ADM' => 'administrator',
        'LDR' => 'leader',
        'DEV' => 'developer',
        'TST' => 'tester',
        'DOC' => 'documenter',
    ];

    public function __construct(
        private AssignUserToRoleAction $assignUserToRole,
    ) {}

    /**
     * Añadir un miembro al proyecto
     */
    public function addMember(Project $project, int $userId, string $roleCode): ProjectUser
    {
        // VALIDACIONES
        $this->validateProject($project);
        $this->validateUserNotInProject($project, $userId);
        $this->validateRoleCode($roleCode);

        // Obtener o crear el rol
        $projectRole = $this->getProjectRole($project, $roleCode);

        // TRANSACCIÓN
        return DB::transaction(function () use ($projectRole, $userId, $roleCode) {

            // PASO 1: Asignar usuario al rol
            $assignment = $this->assignUserToRole->execute($projectRole->id, $userId);

            // PASO 2: Asignar esquema de permisos al rol (si no existe)
            $this->assignPermissionSchemeToRole($projectRole, $roleCode);

            // Cargar relaciones para la respuesta
            return $assignment->load([
                'role',
                'role.permissionScheme.scheme',
                'user'
            ]);
        });
    }

    /**
     * Actualizar el rol de un miembro
     */
    public function updateMemberRole(Project $project, int $userId, string $newRoleCode): ProjectUser
    {
        // VALIDACIONES
        $this->validateProject($project);
        $this->validateRoleCode($newRoleCode);

        // Buscar la asignación actual
        $existingAssignment = ProjectUser::whereHas('role', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })
            ->where('user_id', $userId)
            ->with('role')
            ->firstOrFail();

        // Si el rol es el mismo, no hacer nada
        if ($existingAssignment->role->code === $newRoleCode) {
            return $existingAssignment->load([
                'role',
                'role.permissionScheme.scheme',
                'user'
            ]);
        }

        return DB::transaction(function () use ($project, $userId, $newRoleCode, $existingAssignment) {

            // Obtener o crear el nuevo rol
            $newProjectRole = $this->getProjectRole($project, $newRoleCode);

            // Eliminar la asignación anterior
            $existingAssignment->delete();

            // Crear nueva asignación
            $newAssignment = $this->assignUserToRole->execute($newProjectRole->id, $userId);

            // Asegurar que el nuevo rol tenga su esquema de permisos
            $this->assignPermissionSchemeToRole($newProjectRole, $newRoleCode);

            return $newAssignment->load([
                'role',
                'role.permissionScheme.scheme',
                'user'
            ]);
        });
    }

    /**
     * Eliminar un miembro del proyecto
     */
    public function removeMember(Project $project, int $userId): void
    {
        $this->validateProject($project);

        $deleted = ProjectUser::whereHas('role', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })
            ->where('user_id', $userId)
            ->delete();

        if (!$deleted) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Usuario no encontrado',
                    'reason' => 'El usuario no es miembro de este proyecto',
                    'user_id' => $userId,
                    'project_id' => $project->id,
                ]),
                404
            );
        }
    }

    /**
     * Asignar esquema de permisos al rol
     */
    private function assignPermissionSchemeToRole(ProjectRole $role, string $roleCode): void
    {
        // Verificar si el rol ya tiene un esquema asignado
        $existingPermission = ProjectRolePermission::where('project_role_id', $role->id)->first();

        if ($existingPermission) {
            return;
        }

        // Obtener el esquema por su código
        $scheme = ProjectPermissionScheme::where('code', $roleCode)->first();

        if (!$scheme) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Configuración incompleta',
                    'reason' => 'No hay esquema de permisos definido para el código: ' . $roleCode,
                    'role_code' => $roleCode
                ]),
                500
            );
        }

        // Crear el registro en project_role_permissions
        ProjectRolePermission::create([
            'project_role_id' => $role->id,
            'permission_scheme_id' => $scheme->id,
        ]);
    }

    /**
     * Obtener o crear el rol del proyecto
     */
    private function getProjectRole(Project $project, string $roleCode): ProjectRole
    {
        // Buscar si existe el rol en el proyecto por código
        $role = $project->roles()
            ->where('code', $roleCode)
            ->first();

        // Si no existe, lo creamos
        if (!$role) {
            $roleName = $this->getRoleNameFromCode($roleCode);

            $role = ProjectRole::create([
                'project_id' => $project->id,
                'type' => $roleName,
                'code' => $roleCode,
            ]);
        }

        return $role;
    }

    /**
     * Validar que el código de rol existe
     */
    private function validateRoleCode(string $roleCode): void
    {
        $allowedCodes = ['ADM', 'LDR', 'DEV', 'TST', 'DOC'];

        if (!in_array($roleCode, $allowedCodes)) {
            throw new ProjectException(
                json_encode([
                    'error' => 'Rol inválido',
                    'reason' => 'El código de rol no es válido',
                    'received' => $roleCode,
                    'allowed_codes' => $allowedCodes,
                    'allowed_roles' => [
                        'ADM' => 'Administrator',
                        'LDR' => 'Leader',
                        'DEV' => 'Developer',
                        'TST' => 'Tester',
                        'DOC' => 'Documenter',
                    ]
                ]),
                400
            );
        }
    }

    /**
     * Obtener nombre del rol por su código
     */
    private function getRoleNameFromCode(string $code): string
    {
        return $this->roleNameMap[$code] ?? strtolower($code);
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
                    'reason' => 'No se pueden gestionar miembros en un proyecto eliminado',
                    'project_id' => $project->id,
                    'deleted_at' => $project->deleted_at?->toDateTimeString(),
                ]),
                400
            );
        }
    }

    /**
     * Validar que el usuario no tenga ningún rol en el proyecto
     */
    private function validateUserNotInProject(Project $project, int $userId): void
    {
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
                        'code' => $existingAssignment->role->code ?? 'unknown',
                        'type' => $existingAssignment->role->type,
                        'assignment_id' => $existingAssignment->id,
                    ],
                    'suggestion' => 'Solo se permite un rol por usuario en el proyecto',
                ]),
                400
            );
        }
    }

    /**
     * Obtener todos los miembros del proyecto
     */
    public function getProjectMembers(Project $project): \Illuminate\Database\Eloquent\Collection
    {
        return ProjectUser::whereHas('role', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })
            ->with(['user', 'role.permissionScheme.scheme'])
            ->get();
    }

    /**
     * Obtener un miembro específico del proyecto
     */
    public function getProjectMember(Project $project, int $userId): ?ProjectUser
    {
        return ProjectUser::whereHas('role', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })
            ->where('user_id', $userId)
            ->with(['user', 'role.permissionScheme.scheme'])
            ->first();
    }
}
