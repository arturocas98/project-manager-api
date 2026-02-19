<?php

namespace App\Http\Controllers;

use App\Http\Resources\App\IncidenceCollection;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('App')]
#[Subgroup('Incidence')]
#[Authenticated]
class IncidenceAssignedController extends Controller
{
    public function __construct(){}

    public function index(int $projectId){}
    public function store(int $projectId){}
    public function show(int $projectId){}
    public function update(int $projectId){}
    public function destroy(int $projectId){}
}
