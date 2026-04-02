<?php

namespace App\Enums;

use Datomatic\LaravelEnumHelper\LaravelEnumHelper;

enum Province: int
{
    use LaravelEnumHelper;

    case AZUAY = 1;
    case BOLIVAR = 2;
    case CAÑAR = 3;
    case CARCHI = 4;
    case CHIMBORAZO = 5;
    case COTOPAXI = 6;
    case EL_ORO = 7;
    case GUAYAS = 8;
    case IMBABURA = 9;
    case LOJA = 10;
    case MANABI = 11;
    case MORONA_SANTIAGO = 12;
    case NAPO = 13;
    case ORRELLANA = 14;
    case PASTAZA = 15;
    case PICHINCHA = 16;
    case TUNGURAHUA = 17;
    case ZAMORA_CHINCHIPE = 18;
}
