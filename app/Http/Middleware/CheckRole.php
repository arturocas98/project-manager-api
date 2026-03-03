<?php

namespace App\Http\Middleware;

use App\Models\ProjectPermission;
use App\Models\ProjectUser;
use App\Models\ProjectRole;
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

        if (! $userId) {
            return $this->unauthorizedResponse('Usuario no autenticado');
        }

        // Obtener el rol del usuario
        $roleData = $this->getUserRoleData($projectId, $userId);

        if (! $roleData) {
            return $this->unauthorizedResponse('Usuario no tiene un rol asignado en este proyecto');
        }

        // Verificar si el usuario tiene permiso para realizar esta acción
        if (! $this->hasRequiredPermission($roleData, $method, $request)) {
            return $this->forbiddenResponse($roleData, $method);
        }

        // Adjuntar información del rol al request
        $request->merge([
            'user_role_id' => $roleData->role_id,
            'user_role_name' => $roleData->role_name,
            'user_role_type' => $roleData->role_type,
            'user_permissions' => $roleData->permissions,
        ]);

        return $next($request);
    }

    /**
     * Obtiene el ID del proyecto de la ruta
     */
    private function getProjectId(Request $request)
    {
        $projectId = $request->route('project');
        return is_object($projectId) ? $projectId->id : $projectId;
    }

    /**
     * Obtiene los datos del rol del usuario usando MÉTODOS EXPLÍCITOS
     */
    private function getUserRoleData($projectId, $userId)
    {
        try {
            // Cargar con la relación directa permissions (BelongsToMany)
            $projectUser = ProjectUser::with([
                'role.permissionScheme.scheme.permissions'  // ← Cambio aquí
            ])
                ->where('user_id', $userId)
                ->whereHas('role', function($query) use ($projectId) {
                    $query->where('project_id', $projectId);
                })
                ->first();

            if (! $projectUser || ! $projectUser->role) {
                return $this->getAdminRoleData($projectId, $userId);
            }

            $role = $projectUser->role;

            // Obtener permisos usando la relación directa
            $permissions = $this->getPermissionsFromRole($role);

            return (object) [
                'role_id' => $role->id,
                'role_name' => $role->type,
                'role_type' => $role->type,
                'role_model' => $role,
                'permissions' => $permissions,
            ];

        } catch (\Exception $e) {
            \Log::error('Error en getUserRoleData: ' . $e->getMessage());
            return null;
        }
    }

// VERSIÓN MEJORADA - Usando la relación directa permissions()
    private function getPermissionsFromRole($role)
    {
        $permissions = [];

        if ($role->permissionScheme && $role->permissionScheme->scheme) {
            $scheme = $role->permissionScheme->scheme;

            // ¡DIRECTO! permissions ya es una colección de ProjectPermission
            if ($scheme->permissions) {
                foreach ($scheme->permissions as $permission) {
                    $permissions[] = $permission->key;
                }
            }
        }

        return $permissions;
    }

    /**
     * Verifica si el usuario es administrador
     */
    private function getAdminRoleData($projectId, $userId)
    {
        try {
            $adminUser = ProjectUser::with('role')
                ->where('user_id', $userId)
                ->whereHas('role', function($query) use ($projectId) {
                    $query->where('project_id', $projectId)
                        ->where('type', 'administrators');
                })
                ->first();

            if ($adminUser && $adminUser->role) {
                // Los admins tienen TODOS los permisos
                $allPermissions = ProjectPermission::pluck('key')->toArray();

                return (object) [
                    'role_id' => $adminUser->role->id,
                    'role_name' => 'administrators',
                    'role_type' => 'administrators',
                    'role_model' => $adminUser->role,
                    'permissions' => $allPermissions,
                ];
            }

            return null;

        } catch (\Exception $e) {
            \Log::error('Error en getAdminRoleData: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica si el usuario tiene el permiso requerido
     */
    private function hasRequiredPermission($roleData, $method, Request $request)
    {
        $permissions = $roleData->permissions;

        if (empty($permissions)) {
            return false;
        }

        // También podemos usar el método hasPermission del modelo ProjectRole
        // si necesitamos verificaciones más específicas
        if (isset($roleData->role_model)) {
            // Ejemplo de uso del método explícito del modelo
            // foreach ($this->methodPermissions[$method] as $permissionKey) {
            //     if ($roleData->role_model->hasPermission($permissionKey)) {
            //         return true;
            //     }
            // }
        }

        $requiredPermissions = $this->methodPermissions[$method] ?? [];

        if (empty($requiredPermissions)) {
            return true;
        }

        foreach ($requiredPermissions as $permission) {
            if (in_array($permission, $permissions)) {
                return true;
            }
        }

        return $this->checkSpecificRoutePermissions($request, $permissions);
    }

    /**
     * Verificaciones específicas por ruta
     */
    private function checkSpecificRoutePermissions(Request $request, $permissions)
    {
        $route = $request->route()->getName();

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
     * Método de utilidad para verificar permisos usando el modelo ProjectRole
     */
    private function roleHasPermission($role, $permissionKey)
    {
        // Usamos el método explícito del modelo
        return $role->hasPermission($permissionKey);
    }

    /**
     * Respuesta para usuarios no autorizados
     */
    private function unauthorizedResponse($message)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'UNAUTHORIZED',
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
            'required_permissions' => $this->methodPermissions[$method] ?? [],
            'user_permissions' => $roleData->permissions,
            'user_permissions_count' => count($roleData->permissions),
        ], 403);
    }
}