<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckProjectAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $projectId = $request->route('project');

        // Si es modelo route binding, puede ser objeto
        if (is_object($projectId)) {
            $projectId = $projectId->id;
        }

        $userId = auth()->id();

        $isAdmin = \DB::table('project_roles')
            ->join('project_users', 'project_roles.id', '=', 'project_users.project_role_id')
            ->where('project_roles.project_id', $projectId)
            ->where('project_roles.type', 'Administrators')
            ->where('project_users.user_id', $userId)
            ->exists();

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Acción no permitida. Se requiere rol de Administrador.',
                'error_code' => 'ADMIN_REQUIRED'
            ], 403);
        }

        return $next($request);
    }
}
