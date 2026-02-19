<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Mapeo de métodos HTTP a permisos requeridos
     */
    protected $methodPermissions = [
        'GET' => ['view_projects', 'view_tasks', 'view_files', 'view_reports'],
        'POST' => ['create_projects', 'create_tasks', 'upload_files', 'invite_users'],
        'PUT' => ['edit_projects', 'edit_tasks', 'manage_settings'],
        'PATCH' => ['edit_projects', 'edit_tasks', 'manage_settings'],
        'DELETE' => ['delete_projects', 'delete_tasks', 'delete_files', 'remove_users'],
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $projectId = $this->getProjectId($request);
        $userId = auth()->id();
        $method = $request->method();

        if (!$userId) {
            return $this->unauthorizedResponse('Usuario no autenticado');
        }

        // Obtener el rol del usuario y sus permisos
        $roleData = $this->getUserRoleWithPermissions($projectId, $userId);

        if (!$roleData) {
            return $this->unauthorizedResponse('Usuario no tiene un rol asignado en este proyecto');
        }

        // Verificar si el usuario tiene permiso para realizar esta acción
        if (!$this->hasRequiredPermission($roleData->permissions, $method, $request)) {
            return $this->forbiddenResponse($roleData, $method);
        }

        // Adjuntar información del rol al request para uso posterior
        $request->merge([
            'user_role_id' => $roleData->role_id,
            'user_role_name' => $roleData->role_name,
            'user_role_type' => $roleData->role_type,
            'user_permissions' => $roleData->permissions
        ]);

        return $next($request);
    }

    /**
     * Obtiene el ID del proyecto de la ruta
     */
    private function getProjectId(Request $request)
    {
        $projectId = $request->route('project');

        // Si es modelo route binding, puede ser objeto
        if (is_object($projectId)) {
            $projectId = $projectId->id;
        }

        return $projectId;
    }

    /**
     * Obtiene el rol del usuario y sus permisos en el proyecto
     */
    private function getUserRoleWithPermissions($projectId, $userId)
    {
        try {
            // Obtener el rol del usuario a través de project_users
            $userRole = \DB::table('project_users')
                ->join('project_roles', 'project_users.project_role_id', '=', 'project_roles.id')
                ->where('project_roles.project_id', $projectId)
                ->where('project_users.user_id', $userId)
                ->whereNull('project_users.deleted_at')
                ->select(
                    'project_roles.id as role_id',
                    'project_roles.type as role_type'
                )
                ->first();

            if (!$userRole) {
                return $this->getAdminRole($projectId, $userId);
            }

            // Obtener el esquema de permisos asociado al rol
            $permissionScheme = \DB::table('project_role_permissions')
                ->join('project_permission_schemes', 'project_role_permissions.permission_scheme_id', '=', 'project_permission_schemes.id')
                ->where('project_role_permissions.project_role_id', $userRole->role_id)
                ->select(
                    'project_permission_schemes.id as scheme_id',
                    'project_permission_schemes.name as scheme_name'
                )
                ->first();

            if (!$permissionScheme) {
                return (object)[
                    'role_id' => $userRole->role_id,
                    'role_name' => $this->mapRoleTypeToName($userRole->role_type),
                    'role_type' => $userRole->role_type,
                    'permissions' => []
                ];
            }

            // Obtener los permisos del esquema
            $permissions = \DB::table('scheme_permissions')
                ->join('project_permissions', 'scheme_permissions.project_permission_id', '=', 'project_permissions.id')
                ->where('scheme_permissions.permission_scheme_id', $permissionScheme->scheme_id)
                ->pluck('project_permissions.key')
                ->toArray();

            return (object)[
                'role_id' => $userRole->role_id,
                'role_name' => $permissionScheme->scheme_name,
                'role_type' => $userRole->role_type,
                'permissions' => $permissions
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verifica si el usuario es administrador del proyecto
     */
    private function getAdminRole($projectId, $userId)
    {
        // Verificar si es administrador (type = 'Administrators')
        $isAdmin = \DB::table('project_roles')
            ->join('project_users', 'project_roles.id', '=', 'project_users.project_role_id')
            ->where('project_roles.project_id', $projectId)
            ->where('project_roles.type', 'Administrators')
            ->where('project_users.user_id', $userId)
            ->whereNull('project_users.deleted_at')
            ->exists();

        if ($isAdmin) {
            // Obtener todos los permisos existentes
            $allPermissions = \DB::table('project_permissions')->pluck('key')->toArray();

            return (object)[
                'role_id' => null,
                'role_name' => 'Administrators',
                'role_type' => 'Administrators',
                'permissions' => $allPermissions
            ];
        }

        return null;
    }

    /**
     * Mapea el tipo de rol a un nombre legible
     */
    private function mapRoleTypeToName($roleType)
    {
        $map = [
            'Administrators' => 'Administrators',
            'Project_gestor' => 'Gestor de Proyecto',
            'Developer' => 'Desarrollador',
            'User' => 'Miembro del Equipo',
        ];

        return $map[$roleType] ?? $roleType;
    }

    /**
     * Verifica si el usuario tiene el permiso requerido para el método HTTP
     */
    private function hasRequiredPermission($permissions, $method, Request $request)
    {
        // Si el usuario no tiene permisos, denegar
        if (empty($permissions)) {
            return false;
        }

        // Obtener permisos requeridos para este método
        $requiredPermissions = $this->methodPermissions[$method] ?? [];

        // Si no hay permisos definidos para este método, permitir por defecto
        if (empty($requiredPermissions)) {
            return true;
        }

        // Verificar si tiene ALGUNO de los permisos requeridos
        foreach ($requiredPermissions as $permission) {
            if (in_array($permission, $permissions)) {
                return true;
            }
        }

        // Verificaciones específicas por ruta (opcional)
        return $this->checkSpecificRoutePermissions($request, $permissions);
    }

    /**
     * Verificaciones específicas por ruta
     */
    private function checkSpecificRoutePermissions(Request $request, $permissions)
    {
        $route = $request->route()->getName();
        $method = $request->method();

        // Ejemplo de verificaciones específicas
        $routePermissions = [
            'projects.members.invite' => ['invite_users', 'manage_members'],
            'projects.members.remove' => ['remove_users', 'manage_members'],
            'projects.files.upload' => ['upload_files'],
            'projects.reports.generate' => ['generate_reports'],
        ];

        if (isset($routePermissions[$route])) {
            foreach ($routePermissions[$route] as $permission) {
                if (in_array($permission, $permissions)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Respuesta para usuarios no autorizados
     */
    private function unauthorizedResponse($message)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'UNAUTHORIZED'
        ], 401);
    }

    /**
     * Respuesta para métodos no permitidos
     */
    private function forbiddenResponse($roleData, $method)
    {
        return response()->json([
            'success' => false,
            'message' => "No tienes permisos para realizar la acción '{$method}' con el rol '{$roleData->role_name}'",
            'error_code' => 'FORBIDDEN',
            'role' => $roleData->role_name,
            'role_type' => $roleData->role_type,
            'method' => $method,
            'required_permissions' => $this->methodPermissions[$method] ?? []
        ], 403);
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    public function hasPermission($projectId, $userId, $permissionKey)
    {
        $roleData = $this->getUserRoleWithPermissions($projectId, $userId);
        return $roleData && in_array($permissionKey, $roleData->permissions);
    }

    /**
     * Verifica si el usuario tiene un rol específico por nombre
     */
    public function hasRole($projectId, $userId, $roleName)
    {
        $roleData = $this->getUserRoleWithPermissions($projectId, $userId);
        return $roleData && $roleData->role_name === $roleName;
    }

    /**
     * Verifica si el usuario tiene un tipo de rol específico
     */
    public function hasRoleType($projectId, $userId, $roleType)
    {
        $roleData = $this->getUserRoleWithPermissions($projectId, $userId);
        return $roleData && $roleData->role_type === $roleType;
    }

    /**
     * Obtiene todos los permisos del usuario
     */
    public function getUserPermissions($projectId, $userId)
    {
        $roleData = $this->getUserRoleWithPermissions($projectId, $userId);
        return $roleData ? $roleData->permissions : [];
    }
}