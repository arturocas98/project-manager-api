<?php

namespace App\Http\Controllers;

use App\Http\Resources\App\LocateResource;
use App\Models\Locate;
use Illuminate\Http\Request;

class LocateController extends Controller
{
    public function index(Request $request)
    {
        return LocateResource::collection(Locate::all());
    }
}
