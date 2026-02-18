<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{

    protected $rolePermissions = [
        'Project_gestor' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
        'Developer' => ['GET', 'PUT'],
        'User' => ['GET'],
        'Administrators' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], // Todos los permisos
    ];

    public function handle(Request $request, Closure $next)
    {
        $projectId = $this->getProjectId($request);
        $userId = auth()->id();
        $method = $request->method();

        // Obtener el rol del usuario en el proyecto
        $userRole = $this->getUserRoleInProject($projectId, $userId);

        if (!$userRole) {
            return $this->unauthorizedResponse('Usuario no tiene un rol asignado en este proyecto');
        }

        // Validar si el método HTTP está permitido para el rol
        if (!$this->isMethodAllowedForRole($userRole, $method)) {
            return $this->forbiddenResponse($userRole, $method);
        }

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
     * Obtiene el rol del usuario en el proyecto
     */
    private function getUserRoleInProject($projectId, $userId)
    {
        // Primero verificamos si el usuario tiene un rol en el proyecto
        $userRole = \DB::table('project_users')
            ->join('project_roles', 'project_users.project_role_id', '=', 'project_roles.id')
            ->join('project_permission_schemes', 'project_roles.permission_scheme_id', '=', 'project_permission_schemes.id')
            ->where('project_roles.project_id', $projectId)
            ->where('project_users.user_id', $userId)
            ->select(
                'project_permission_schemes.name as role_name',
                'project_roles.type as role_type'
            )
            ->first();

        if ($userRole) {
            return $userRole->role_name;
        }

        // Si no encuentra rol en project_users, verificamos en la tabla project_roles directamente
        $directRole = \DB::table('project_roles')
            ->join('project_permission_schemes', 'project_roles.permission_scheme_id', '=', 'project_permission_schemes.id')
            ->where('project_roles.project_id', $projectId)
            ->where('project_roles.type', 'Administrators')
            ->select('project_permission_schemes.name as role_name')
            ->first();

        return $directRole?->role_name;
    }

    /**
     * Verifica si el método HTTP está permitido para el rol
     */
    private function isMethodAllowedForRole($roleName, $method)
    {
        // Verificar si el rol existe en nuestro mapeo de permisos
        if (!isset($this->rolePermissions[$roleName])) {
            return false;
        }

        // Obtener los métodos permitidos para este rol
        $allowedMethods = $this->rolePermissions[$roleName];

        return in_array($method, $allowedMethods);
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
    private function forbiddenResponse($roleName, $method)
    {
        $allowedMethods = implode(', ', $this->rolePermissions[$roleName] ?? []);

        return response()->json([
            'success' => false,
            'message' => "Acción no permitida para el rol '{$roleName}'. Métodos permitidos: {$allowedMethods}",
            'error_code' => 'METHOD_NOT_ALLOWED_FOR_ROLE',
            'role' => $roleName,
            'allowed_methods' => $this->rolePermissions[$roleName] ?? []
        ], 403);
    }

    /**
     * Método adicional para verificar si el usuario tiene un rol específico
     */
    public function hasRole($projectId, $userId, $roleName)
    {
        $userRole = $this->getUserRoleInProject($projectId, $userId);
        return $userRole === $roleName;
    }

    /**
     * Método adicional para obtener todos los roles de un usuario en un proyecto
     */
    public function getUserRoles($projectId, $userId)
    {
        return \DB::table('project_users')
            ->join('project_roles', 'project_users.project_role_id', '=', 'project_roles.id')
            ->join('project_permission_schemes', 'project_roles.permission_scheme_id', '=', 'project_permission_schemes.id')
            ->where('project_roles.project_id', $projectId)
            ->where('project_users.user_id', $userId)
            ->select('project_permission_schemes.name as role_name')
            ->get();
    }
}
