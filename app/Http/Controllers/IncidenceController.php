<?php

namespace App\Http\Controllers;

use App\Http\Requests\App\StoreIncidenceRequest;
use App\Http\Requests\App\UpdateIncidenceRequest;
use App\Http\Resources\App\IncidenceCollection;
use App\Http\Resources\App\IncidenceResource;
use App\Models\Incidence;
use App\Models\Project;
use App\Services\Incidence\CreateIndiceService;
use App\Services\Incidence\IncidenceService;
use App\Services\Incidence\UpdateIncidenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Subgroup;


#[Group('App')]
#[Subgroup('Incidence')]
#[Authenticated]
class IncidenceController extends Controller
{
    public function __construct(
        private IncidenceService $incidenceService,
        private CreateIndiceService $createIndiceService,
        private UpdateIncidenceService $updateIncidenceService,
    ) {}
    #[ResponseFromApiResource(IncidenceCollection::class, Incidence::class, collection: true)]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    #[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
    #[ResponseFromFile(file: 'responses/404.json', status: JsonResponse::HTTP_NOT_FOUND)]
    public function index(int $projectId): IncidenceCollection
    {
        $project = Project::findOrFail($projectId);

        $this->incidenceService->validateProjectAccess($project);

        $incidences = $this->incidenceService->getProjectIncidences($projectId);

        return new IncidenceCollection($incidences, $project->id, $project->name);
    }

    #[ResponseFromApiResource(IncidenceResource::class, Incidence::class)]
    #[ResponseFromFile(file: 'responses/401.json', status: JsonResponse::HTTP_UNAUTHORIZED)]
    #[ResponseFromFile(file: 'responses/403.json', status: JsonResponse::HTTP_FORBIDDEN)]
    #[ResponseFromFile(file: 'responses/404.json', status: JsonResponse::HTTP_NOT_FOUND)]
    #[ResponseFromFile(file: 'responses/422.json', status: JsonResponse::HTTP_UNPROCESSABLE_ENTITY)]
    public function store(StoreIncidenceRequest $request, int $projectId): IncidenceResource
    {
        $project = Project::findOrFail($projectId);

        $this->createIndiceService->validateProjectAccess($project);

        $incidence = $this->createIndiceService->createIncidence(
            $projectId,
            $request->validated(),
            auth()->id()
        );

        $incidence = $this->createIndiceService->loadIncidenceRelations($incidence);

        return new IncidenceResource($incidence);
    }
    public function show(int $project, int $incidence): IncidenceResource
    {
        $projectModel = Project::findOrFail($project);

        $this->incidenceService->validateProjectAccess($projectModel);

        $incidenceModel = Incidence::where('project_id', $project)
            ->where('id', $incidence)
            ->firstOrFail();

        return new IncidenceResource($incidenceModel);
    }
    public function update(UpdateIncidenceRequest $request, int $projectId, int $incidenceId): IncidenceResource
    {
        $project = Project::findOrFail($projectId);

        $this->createIndiceService->validateProjectAccess($project);

        $incidence = $this->updateIncidenceService->update(
            $incidenceId,
            $request->validated(),
            auth()->id()
        );

        $incidence = $this->createIndiceService->loadIncidenceRelations($incidence);

        return new IncidenceResource($incidence);
    }
    public function destroy($id) {}
}
