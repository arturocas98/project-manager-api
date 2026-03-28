<?php

use App\Enums\Province;
use App\Enums\UserModality;

return [
    UserModality::class => [
        UserModality::Presentail->value => 'Presencial',
        UserModality::HomeOffice->value => 'Teletrabajo',
    ],
    Province::class => [
        Province::AZUAY->value => 'Azuay',
        Province::BOLIVAR->value => 'Bolívar',
        Province::CAÑAR->value => 'Cañar',
        Province::CARCHI->value => 'Carchi',
        Province::CHIMBORAZO->value => 'Chimborazo',
        Province::COTOPAXI->value => 'Cotopaxi',
        Province::EL_ORO->value => 'El Oro',
        Province::GUAYAS->value => 'Guayas',
        Province::IMBABURA->value => 'Imbabura',
        Province::LOJA->value => 'Loja',
        Province::MANABI->value => 'Manabí',
        Province::MORONA_SANTIAGO->value => 'Morona Santiago',
        Province::NAPO->value => 'Napo',
        Province::ORRELLANA->value => 'Orellana',
        Province::PASTAZA->value => 'Pastaza',
        Province::PICHINCHA->value => 'Pichincha',
        Province::TUNGURAHUA->value => 'Tungurahua',
        Province::ZAMORA_CHINCHIPE->value => 'Zamora Chinchipe',
    ],
];
