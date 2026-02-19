<?php

namespace App\Http\Controllers;

use App\Http\Requests\App\AddProjectMemberRequest;
use App\Http\Requests\App\UpdateMemberRoleRequest;
use App\Http\Resources\App\ProjectMemberRemovedResource;
use App\Http\Resources\App\ProjectMemberResource;
use App\Http\Resources\App\ProjectMemberRoleUpdatedResource;
use App\Http\Resources\App\ProjectMembersResource;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Services\Project\DeletProjectMemberService;
use App\Services\Project\IndexProjectMemberService;
use App\Services\Project\ProjectMemberService;
use App\Services\Project\UpdateProjectMemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('App')]
#[Subgroup('Project')]
#[Authenticated]
class ProjectMemberController extends Controller
{
    public function __construct(
        private ProjectMemberService $projectMemberService,
        private IndexProjectMemberService $indexProjectMemberService,
        private DeletProjectMemberService $deletProjectMemberService,
        private UpdateProjectMemberService $updateProjectMemberService,
    ) {}

    public function show(Request $request, Project $project)
    {

    }

    #[ResponseFromApiResource(
        ProjectMemberResource::class,
        ProjectUser::class,
        collection: true
    )]
    public function index(Request $request, int $projectId)
    {
        $project = Project::findOrFail($projectId);

        $result = $this->indexProjectMemberService->getMembers($project, $request);

        return ProjectMemberResource::collection($result['members'])
            ->additional([
                'success' => true,
                'stats' => $result['stats'],
                'filters' => $result['filters'],
            ]);
    }

    #[ResponseFromApiResource(
        ProjectMemberResource::class,
        ProjectUser::class,
        status: JsonResponse::HTTP_CREATED
    )]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    #[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
    #[ResponseFromFile(file: 'responses/404.json', status: JsonResponse::HTTP_NOT_FOUND)]
    #[Response(
        content: ['message' => 'El usuario ya es miembro del proyecto'],
        status: JsonResponse::HTTP_CONFLICT,
        description: 'User already a member'
    )]
    #[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
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

    #[ResponseFromApiResource(ProjectMemberRemovedResource::class, ProjectUser::class, description: 'Member removed successfully')]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    #[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
    #[ResponseFromFile(file: 'responses/404.json', status: JsonResponse::HTTP_NOT_FOUND)]
    public function destroy(int $projectId, int $memberId)
    {
        $project = Project::findOrFail($projectId);

        $result = $this->deletProjectMemberService->removeMember($project, $memberId);

        return new ProjectMemberRemovedResource($result);
    }

    #[ResponseFromApiResource(ProjectMemberResource::class, ProjectUser::class, description: 'Role updated successfully')]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    #[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
    #[ResponseFromFile(file: 'responses/404.json', status: JsonResponse::HTTP_NOT_FOUND)]
    #[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
    public function updateRole(UpdateMemberRoleRequest $request, int $projectId, int $memberId)
    {
        $project = Project::findOrFail($projectId);

        $result = $this->updateProjectMemberService->updateRole(
            $project,
            $memberId,
            $request->role_type
        );

        return new ProjectMemberResource($result);
    }
}
