<?php

namespace App\Http\Controllers;

use App\Enums\Canton;
use App\Enums\Province;
use App\Http\Resources\App\EnumResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('App')]
#[Subgroup('Canton')]
#[Authenticated]
class CantonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Filtra por provincia con el query `data` (id de {@see Province}, coincide con el campo `data` del recurso).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $cantons = collect(Canton::cases());

        if ($request->filled('data')) {
            $province = Province::tryFrom((int) $request->query('data'));
            $cantons = $province !== null
                ? $cantons->filter(fn(Canton $canton) => $canton->data() === $province)
                : collect();
        }

        return EnumResource::collection(
            $cantons->sortBy(fn(Canton $canton) => $canton->description())
        );
    }
}
