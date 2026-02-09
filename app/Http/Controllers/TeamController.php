<?php

namespace App\Http\Controllers;

use App\Actions\App\SaveTeamAction;
use App\Actions\App\SaveTeamUserAction;
use App\Http\Queries\App\TeamQuery;
use App\Http\Requests\App\FriendRequest;
use App\Http\Requests\App\TeamRequest;
use App\Http\Requests\App\TeamUserRequest;
use App\Http\Resources\App\TeamResource;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('App')]
#[Subgroup('Team')]
#[Authenticated]
class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[ResponseFromApiResource(TeamResource::class, Team::class, collection: true)]
    public function index(TeamQuery $query)
    {
        return TeamResource::collection($query->result());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TeamRequest $request, SaveTeamAction $save): TeamResource
    {
        $team = $save($request->validated());

        return new TeamResource($team);
    }

    public function Team_add(TeamUserRequest $request, SaveTeamUserAction $save):TeamResource
    {
        $team_user = $save($request->validated());
        return new TeamResource($team_user);
    }

    public function Friend_request_email(FriendRequest $request){
        //enviar el email al usuario para aceptar la solicitud
    }

    public  function Friend()
    {

    }
    /**
     * Display the specified resource.
     */
    public function show(Team $team):TeamResource
    {
        return new TeamResource($team);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TeamRequest $request, Team $team, SaveTeamAction $save): TeamResource
    {
        $team = $save($request->validated(), $team);

        return new TeamResource($team);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $team->delete();
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
