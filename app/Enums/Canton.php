<?php

namespace App\Enums;

use Datomatic\LaravelEnumHelper\LaravelEnumHelper;

enum Canton: int
{
    use LaravelEnumHelper;

    case CUENCA = 1;
    case GIRON = 2;
    case GUALACEO = 3;
    case NABON = 4;
    case PAUTE = 5;
    case PUCARA = 6;
    case SAN_FERNANDO = 7;
    case SANTA_ISABEL = 8;
    case SIGSIG = 9;
    case ONA = 10;
    case CHORDELEG = 11;
    case EL_PAN = 12;
    case SEVILLA_DE_ORO = 13;
    case GUACHAPALA = 14;
    case CAMILO_PONCE_ENRIQUEZ = 15;

    public function data(): Province
    {
        return match ($this) {
            self::CUENCA => Province::AZUAY,
            self::GIRON => Province::AZUAY,
            self::GUALACEO => Province::CAÑAR,
            self::NABON => Province::CARCHI,
            self::PAUTE => Province::CHIMBORAZO,
            self::PUCARA => Province::COTOPAXI,
            self::SAN_FERNANDO => Province::EL_ORO,
            self::SANTA_ISABEL => Province::GUAYAS,
            self::SIGSIG => Province::IMBABURA,
            self::ONA => Province::LOJA,
            self::CHORDELEG => Province::MANABI,
            self::EL_PAN => Province::MORONA_SANTIAGO,
            self::SEVILLA_DE_ORO => Province::ZAMORA_CHINCHIPE,
            self::GUACHAPALA => Province::TUNGURAHUA,
            self::CAMILO_PONCE_ENRIQUEZ => Province::PICHINCHA,
        };
    }
}
