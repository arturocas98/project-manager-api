<?php

namespace App\Http\Controllers;

use App\Actions\App\Recent\CreateRecentAction;
use App\Exceptions\ProjectException;
use App\Http\Queries\App\ProjectQuery;
use App\Http\Requests\App\ProjectRequest;
use App\Http\Requests\App\UpdateProjectRequest;
use App\Http\Resources\App\OneProjectResource;
use App\Http\Resources\App\ProjectCreatedResource;
use App\Http\Resources\App\ProjectResource;
use App\Http\Resources\App\ProjectSummaryResource;
use App\Http\Resources\Auth\UserProyectCollection;
use App\Models\Project;
use App\Services\Project\ProjectCreationService;
use App\Services\Project\ProjectDeleteService;
use App\Services\Project\ProjectSummaryService;
use App\Services\Project\ProjectUpdateService;
use App\Services\Project\ProjectUsersServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('App')]
#[Subgroup('Project')]
#[Authenticated]
class ProjectController extends Controller
{
    public function __construct(
        private ProjectCreationService $projectCreationService,
        private ProjectUpdateService  $projectUpdateService,
        private ProjectDeleteService $projectDeleteService,
        private ProjectSummaryService $summaryService,
        private ProjectUsersServices $projectUserService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    #[ResponseFromApiResource(
        ProjectResource::class,
        Project::class, // AÑADIR modelo explícitamente
        collection: true,
    )]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    public function index(ProjectQuery $query): AnonymousResourceCollection
    {
        $projects = $query->paginate();

        return ProjectResource::collection($projects);
    }

    public function summary(Project $project, Request $request)
    {
        $summaryData = $this->summaryService->getSummary($project->id);

        return new ProjectSummaryResource($summaryData);
    }

    public function getUnassignedUsers(int $projectId, Request $request): UserProyectCollection
    {
        $authenticatedUserId = $request->user()->id;

        $users = $this->projectUserService->getUnassignedUsers(
            $projectId,
            $authenticatedUserId
        );

        return new UserProyectCollection($users);
    }

    public function myRole(Project $project, Request $request)
    {
        $userId = $request->user()->id;
        $roles = $project->getUserRoles($userId);

        return response()->json([
            'role_type' => $roles->first()?->type,
        ]);
    }

    /**
     * Display the specified resource.
     */
    #[ResponseFromApiResource(
        ProjectCreatedResource::class,
        Project::class, // AÑADIR modelo explícitamente
        status: JsonResponse::HTTP_CREATED
    )]
    #[Response(content: [
        'success' => false,
        'message' => 'Proyecto no encontrado o no tienes acceso',
        'error_code' => 'PROJECT_NOT_FOUND'
    ], status: JsonResponse::HTTP_NOT_FOUND, description: 'Project not found')]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    public function show(ProjectQuery $query, Project $project)
    {
        // El route model binding trae un modelo, pero necesitamos cargar las relaciones
        // correctas usando ProjectQuery para que OneProjectResource pueda accederlas.
        $loadedProject = $query->findForShow($project->id);

        if (! $loadedProject) {
            return response()->json([
                'success' => false,
                'message' => 'Proyecto no encontrado o no tienes acceso',
                'error_code' => 'PROJECT_NOT_FOUND',
            ], 404);
        }

        return new OneProjectResource($loadedProject);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[ResponseFromApiResource(ProjectCreatedResource::class, Project::class)]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    #[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
    #[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
    #[Response(content: ['message' => 'Error inesperado al crear proyecto'], status: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: 'Server error')]
    public function store(ProjectRequest $request): ProjectCreatedResource
    {
        try {
            $result = $this->projectCreationService->create($request->validated());
            return new ProjectCreatedResource((object) $result);
        } catch (ProjectException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ProjectException('Error inesperado al crear proyecto', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    #[ResponseFromApiResource(ProjectResource::class, Project::class)]
    #[Response(content: [
        'success' => true,
        'message' => 'Proyecto actualizado exitosamente',
        'data' => '...'
    ], status: JsonResponse::HTTP_OK, description: 'Project updated successfully')]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    #[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
    #[ResponseFromFile(file: 'responses/404.json', status: JsonResponse::HTTP_NOT_FOUND)]
    #[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
    public function update(UpdateProjectRequest $request, int $id, ProjectQuery $query)
    {
        $project = Project::findOrFail($id);

        $updatedProject = $this->projectUpdateService->update(
            $project,
            $request->validated()
        );

        // Cargar las relaciones del update usando el Query object para consistencia
        $updatedProject->load([
            'admin',
            'projectState',
            'roles' => function ($q) {
                $q->whereHas('users', fn ($q) => $q->where('user_id', auth()->id()))
                  ->with(['permissionScheme.scheme.permissions']);
            }
        ]);

        return new ProjectResource($updatedProject);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Response(
        content: [
            'success' => true,
            'message' => 'Proyecto eliminado exitosamente',
            'data' => [
                'id' => 'integer',
                'deleted_at' => 'datetime'
            ]
        ],
        status: JsonResponse::HTTP_OK,
        description: 'Project deleted successfully'
    )]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    #[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
    #[ResponseFromFile(file: 'responses/404.json', status: JsonResponse::HTTP_NOT_FOUND)]
    public function destroy(int $id): JsonResponse
    {
        $project = Project::findOrFail($id);

        $this->projectDeleteService->delete($project);

        return response()->json([
            'data' => [
                'id' => $project->id,
                'deleted_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Get all files associated with a project without user authentication.
     */
    public function files(Project $project)
    {
        $messagesIds = \App\Models\Message::where('project_id', $project->id)->pluck('id');
        
        $incidencesIds = \App\Models\Incidence::where('project_id', $project->id)->pluck('id');
        // Archivos asociados a los comentarios de las tareas
        $taskComentIds = \App\Models\TaskComent::whereIn('incidence_id', $incidencesIds)->pluck('id');

        $messageMorphs = ['messages', \App\Models\Message::class, '2'];
        $taskComentMorphs = ['task_coments', \App\Models\TaskComent::class, '1'];
        $projectMorphs = ['projects', \App\Models\Project::class];

        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::where(function($query) use ($messagesIds, $messageMorphs) {
            $query->whereIn('model_type', $messageMorphs)
                  ->whereIn('model_id', $messagesIds);
        })->orWhere(function($query) use ($taskComentIds, $incidencesIds, $taskComentMorphs) {
            $query->whereIn('model_type', $taskComentMorphs)
                  ->where(function($q) use ($taskComentIds, $incidencesIds) {
                      $q->whereIn('model_id', $taskComentIds)
                        ->orWhereIn('model_id', $incidencesIds);
                  });
        })->orWhere(function($query) use ($project, $projectMorphs) {
            $query->whereIn('model_type', $projectMorphs)
                  ->where('model_id', $project->id);
        })->latest()->get();

        return \App\Http\Resources\App\MediaResource::collection($media);
    }
}
