<?php

namespace App\Http\Controllers;

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
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('App')]
#[Subgroup('Project')]
#[Authenticated]
class ProjectController extends Controller
{
    public function __construct(
        private ProjectCreationService $projectCreationService,
        private ProjectUpdateService $projectUpdateService,
        private ProjectDeleteService $projectDeleteService,
    ) {}
    /**
     * Display a listing of the resource.
     */
    #[ResponseFromApiResource(ProjectResource::class, Project::class, collection: true)]
    public function index(ProjectQuery $query): AnonymousResourceCollection
    {
        $projects = $query->paginate();
        return ProjectResource::collection($projects);
    }

    /**
     * Display the specified resource.
     */
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

        return new OneProjectResource($project);
    }



    /**
     * Store a newly created resource in storage.
     */
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
