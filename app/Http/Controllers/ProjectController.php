<?php

namespace App\Http\Controllers;

use App\Actions\App\SaveProjectAction;
use App\Http\Queries\App\ProjectQuery;
use App\Http\Requests\App\ProjectRequest;
use App\Http\Resources\App\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('App')]
#[Subgroup('Project')]
#[Authenticated]
class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[ResponseFromApiResource(ProjectResource::class, Project::class, collection: true)]
    public function index(ProjectQuery $query): AnonymousResourceCollection
    {
        return ProjectResource::collection($query->result());
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): ProjectResource
    {
        return new ProjectResource($project);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request, SaveProjectAction $save): ProjectResource
    {
    
        $project = $save($request->validated());
        return new ProjectResource($project);
    }

    public function addParticipant(ProjectRequest $request, SaveProjectAction $save): ProjectResource
    {
        $project = $save($request->validated());

        return new ProjectResource($project);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project, SaveProjectAction $save): ProjectResource
    {
        $project = $save($request->validated(), $project);

        return new ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
