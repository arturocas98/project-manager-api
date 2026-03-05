<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class CheckRole
{
    protected array $routePermissions = [
        // Proyectos
        'projects.index' => 'view_projects',
        'projects.show' => 'view_projects',
        'projects.store' => 'create_projects',
        'projects.destroy' => 'delete_projects',
        'projects.update' => 'edit_projects',
        'projects.summary' => 'view_projects',
        'projects.unassigned-users' => 'view_projects',

        // Miembros
        'projects.members.index' => 'view_projects',
        'projects.members.show' => 'view_projects',
        'projects.members.store' => 'manage_members',
        'projects.members.updateRole' => 'manage_members',
        'projects.members.destroy' => 'manage_members',

        // Incidencias (Tareas)
        'projects.incidences.index' => 'view_tasks',
        'projects.incidences.show' => 'view_tasks',
        'projects.incidences.store' => 'create_tasks',
        'projects.incidences.update' => 'edit_tasks',
        'projects.incidences.destroy' => 'delete_tasks',

        // Asignación de incidencias
        'incidences.assignment.show' => 'view_tasks',
        'incidences.assignment.store' => 'assign_tasks',
        'incidences.assignment.update' => 'assign_tasks',
        'incidences.assignment.destroy' => 'assign_tasks',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Usuario no autenticado');
        }

        // ✅ VALIDACIÓN DE ROL GLOBAL CON SPATIE
        // Si el usuario es Admin (rol global), permitir todo sin restricciones
        if ($user->hasRole('Admin')) {
            return $next($request);
        }

        // Obtener el ID del proyecto de la ruta
        $projectId = $this->getProjectIdFromRoute($request);

        if (!$projectId) {
            // Si no hay proyecto en la ruta, verificar si es una ruta global
            return $this->checkGlobalPermission($request, $next);
        }

        // Obtener el proyecto
        $project = \App\Models\Project::find($projectId);

        if (!$project) {
            abort(404, 'Proyecto no encontrado');
        }

        // Verificar si el usuario tiene acceso al proyecto
        if (!$project->hasUserAccess($user->id)) {
            abort(403, 'No tienes acceso a este proyecto');
        }

        // Obtener el permiso requerido para esta ruta
        $requiredPermission = $this->getRequiredPermission($request);

        if (!$requiredPermission) {
            // Si no hay permiso requerido específico, permitir acceso básico al proyecto
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso requerido
        if (!$this->userHasPermission($user, $project, $requiredPermission)) {
            abort(403, 'No tienes permiso para realizar esta acción');
        }

        // Compartir el proyecto con la vista para uso posterior
        if ($request->route()->hasParameter('project')) {
            $request->route()->setParameter('project', $project);
        }

        return $next($request);
    }

    /**
     * Obtener ID del proyecto de la ruta
     */
    protected function getProjectIdFromRoute(Request $request): ?int
    {
        // Buscar en diferentes posibles nombres de parámetros
        $projectId = $request->route('project') ??
            $request->route('project_id') ??
            $request->route('id');

        // Si es un modelo, obtener el ID
        if (is_object($projectId) && method_exists($projectId, 'getKey')) {
            return $projectId->getKey();
        }

        return is_numeric($projectId) ? (int) $projectId : null;
    }

    /**
     * Obtener el permiso requerido para la ruta actual
     */
    protected function getRequiredPermission(Request $request): ?string
    {
        $routeName = $request->route()->getName();

        return $this->routePermissions[$routeName] ?? null;
    }

    /**
     * Verificar si el usuario tiene un permiso específico en el proyecto
     */
    protected function userHasPermission($user, $project, string $permissionKey): bool
    {
        // Obtener todos los roles del usuario en el proyecto
        $userRoles = $project->getUserRoles($user->id);

        foreach ($userRoles as $role) {
            // Verificar si el rol tiene el permiso requerido
            if ($role->permissionScheme &&
                $role->permissionScheme->scheme &&
                in_array($permissionKey, $role->permissionScheme->scheme->permissionsList)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar permisos globales (sin proyecto específico)
     */
    protected function checkGlobalPermission(Request $request, Closure $next): Response
    {
        $routeName = $request->route()->getName();
        $user = $request->user();

        $globalPermissions = [
            'projects.store' => 'create_projects',
            'projects.destroy' => 'delete_projects',
            'projects.update' => 'edit_projects',
            'projects.members.store' => 'manage_members',
            'projects.members.updateRole' => 'manage_members',
            'projects.members.destroy' => 'manage_members',
        ];

        if (isset($globalPermissions[$routeName])) {
            // ✅ VALIDACIÓN ESPECÍFICA PARA projects.store
            if ($routeName === 'projects.store') {
                // Solo Admin puede crear proyectos
                if (!$user->hasRole('Admin')) {
                    abort(403, 'Solo usuarios con rol Admin pueden crear proyectos');
                }

                // Si es Admin, permitir crear proyectos sin restricciones
                return $next($request);
            }

            // Para otros permisos globales, verificar si el usuario tiene algún rol
            if (!$user->projects()->exists()) {
                // Si no tiene proyectos, podría crear el primero
                return $next($request);
            }
        }

        return $next($request);
    }
}