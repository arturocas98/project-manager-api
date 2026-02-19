<?php

namespace App\Http\Controllers;

use App\Http\Requests\App\AddProjectMemberRequest;
use App\Http\Requests\App\UpdateMemberRoleRequest;
use App\Http\Resources\App\ProjectMemberCollection;
use App\Http\Resources\App\ProjectMemberRemovedResource;
use App\Http\Resources\App\ProjectMemberResource;
use App\Http\Resources\App\ProjectMemberRoleUpdatedResource;
use App\Http\Resources\App\ProjectMembersResource;
use App\Models\Project;
use App\Services\Project\DeletProjectMemberService;
use App\Services\Project\IndexProjectMemberService;
use App\Services\Project\ProjectMemberService;
use App\Services\Project\UpdateProjectMemberService;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function __construct(
        private ProjectMemberService $projectMemberService,
        private IndexProjectMemberService $indexprojectMemberService,
        private DeletProjectMemberService $deletprojectMemberService,
        private UpdateProjectMemberService $updateprojectMemberService,
    ) {}

    public function show(Request $request, Project $project)
    {

    }

    public function index(Request $request, int $projectId)
    {
        $project = Project::findOrFail($projectId);

        $result = $this->indexprojectMemberService->getMembers($project, $request);

        return response()->json([
            'success' => true,
            'data' => new ProjectMembersResource($result['members']),
            'stats' => $result['stats'],
            'filters' => $result['filters']
        ]);
    }

    public function store(AddProjectMemberRequest $request, int $projectId)
    {
        $project = Project::findOrFail($projectId);

        $result = $this->projectMemberService->addMember(
            $project,
            $request->user_id,
            $request->role_type
        );

        return response()->json([
            'success' => true,
            'message' => 'Miembro añadido exitosamente al proyecto',
            'data' => new ProjectMemberResource((object) $result)
        ], 201);
    }

    public function destroy(int $projectId, int $memberId)
    {
        $project = Project::findOrFail($projectId);

        $result = $this->deletprojectMemberService->removeMember($project, $memberId);

        return new ProjectMemberRemovedResource($result);
    }

    public function updateRole(UpdateMemberRoleRequest $request, int $projectId, int $memberId)
    {
        $project = Project::findOrFail($projectId);

        $result = $this->updateprojectMemberService->updateRole(
            $project,
            $memberId,
            $request->role_type
        );

        return new ProjectMemberRoleUpdatedResource($result);
    }
}
