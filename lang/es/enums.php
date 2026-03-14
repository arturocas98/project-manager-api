<?php

use App\Enums\UserModality;

return [
    UserModality::class => [
        UserModality::Presentail->value => 'Presencial',
        UserModality::HomeOffice->value => 'Teletrabajo',
    ],
];
