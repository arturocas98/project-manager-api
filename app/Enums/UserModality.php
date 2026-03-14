<?php

namespace App\Enums;

use Datomatic\LaravelEnumHelper\LaravelEnumHelper;

enum UserModality: int
{

    use LaravelEnumHelper;

    case Presentail = 1;
    case HomeOffice = 2;
}
