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
use App\Models\Project;
use App\Services\Project\ProjectCreationService;
use App\Services\Project\ProjectDeleteService;
use App\Services\Project\ProjectUpdateService;
use Illuminate\Http\JsonResponse;
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
        private CreateRecentAction $createRecent,
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
    public function show(ProjectQuery $query, int $id)
    {
        $project = $query->findForShow($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Proyecto no encontrado o no tienes acceso',
                'error_code' => 'PROJECT_NOT_FOUND'
            ], 404);
        }

        $data = [
            'title' => $project->name,
            'link' => "project/$project->id",
            'project_id' => $project->id,
            'icon' => "ph ph-rocket",
        ];
        $this->createRecent->execute($data);

        return new OneProjectResource($project);
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
    public function update(UpdateProjectRequest $request, int $id)
    {
        $project = Project::findOrFail($id);

        $updatedProject = $this->projectUpdateService->update(
            $project,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Proyecto actualizado exitosamente',
            'data' => new ProjectResource($updatedProject)
        ], 200);
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
            'success' => true,
            'message' => 'Proyecto eliminado exitosamente',
            'data' => [
                'id' => $project->id,
                'deleted_at' => now()->toDateTimeString()
            ]
        ]);
    }
}
