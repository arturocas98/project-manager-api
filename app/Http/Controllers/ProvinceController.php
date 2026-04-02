<?php

namespace App\Http\Controllers;

use App\Enums\Province;
use App\Http\Resources\App\EnumResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('App')]
#[Subgroup('Province')]
#[Authenticated]
class ProvinceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return EnumResource::collection(
            collect(Province::cases())
                ->sortBy(
                    fn($province) =>  $province->description()
                )
        );
    }
}
