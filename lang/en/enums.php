<?php

use App\Enums\UserModality;

return [
    UserModality::class => [
        UserModality::Presentail->value => 'Presential',
        UserModality::HomeOffice->value => 'Home Office',
    ],
];
