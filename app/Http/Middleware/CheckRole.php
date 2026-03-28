<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    protected array $routePermissions = [

        'projects.index' => 'view_projects',
        'projects.show' => 'view_projects',
        'projects.store' => 'create_projects',
        'projects.destroy' => 'delete_projects',
        'projects.update' => 'edit_projects',
        'projects.summary' => 'view_projects',
        'projects.unassigned-users' => 'view_projects',


        'projects.members.index' => 'view_projects',
        'projects.members.show' => 'view_projects',
        'projects.members.store' => 'manage_members',
        'projects.members.updateRole' => 'manage_members',
        'projects.members.destroy' => 'manage_members',


        'projects.incidences.index' => 'view_tasks',
        'projects.incidences.show' => 'view_tasks',
        'projects.incidences.store' => 'create_tasks',
        'projects.incidences.update' => 'edit_tasks',
        'projects.incidences.destroy' => 'delete_tasks',


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



        if ($user->hasRole('Admin')) {
            return $next($request);
        }


        $projectId = $this->getProjectIdFromRoute($request);

        if (!$projectId) {

            return $this->checkGlobalPermission($request, $next);
        }


        $project = \App\Models\Project::find($projectId);

        if (!$project) {
            abort(404, 'Proyecto no encontrado');
        }


        if (!$project->hasUserAccess($user->id)) {
            abort(422, 'No tienes acceso a este proyecto');
        }


        $requiredPermission = $this->getRequiredPermission($request);

        if (!$requiredPermission) {

            return $next($request);
        }


        if (!$this->userHasPermission($user, $project, $requiredPermission)) {
            abort(422, 'No tienes permiso para realizar esta acción');
        }


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

        $projectId = $request->route('project') ??
            $request->route('project_id') ??
            $request->route('id');


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

        $userRoles = $project->getUserRoles($user->id);

        foreach ($userRoles as $role) {

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

            if ($routeName === 'projects.store') {

                if (!$user->hasRole('Admin')) {
            abort(422, 'Solo usuarios con rol Admin pueden crear proyectos');
        }


                return $next($request);
            }


            if (!$user->projects()->exists()) {

                return $next($request);
            }
        }

        return $next($request);
    }
}