<?php

namespace App\Enum;

/**
 * Enum representing EU countries for the Alternative Task.
 */
enum AlternativeTaskEuCountry: string
{
    case AT = 'AT';
    case BE = 'BE';
    case BG = 'BG';
    case CY = 'CY';
    case CZ = 'CZ';
    case DE = 'DE';
    case DK = 'DK';
    case EE = 'EE';
    case ES = 'ES';
    case FI = 'FI';
    case FR = 'FR';
    case GR = 'GR';
    case HR = 'HR';
    case HU = 'HU';
    case IE = 'IE';
    case IT = 'IT';
    case LT = 'LT';
    case LU = 'LU';
    case LV = 'LV';
    case MT = 'MT';
    case NL = 'NL';
    case PL = 'PL';
    case PT = 'PT';
    case RO = 'RO';
    case SE = 'SE';
    case SI = 'SI';
    case SK = 'SK';

    /**
     * Checks if a given country code belongs to an EU country.
     */
    public static function isEuCountry(string $countryCode): bool
    {
        foreach (self::cases() as $case) {
            if ($case->value === $countryCode) {
                return true;
            }
        }

        return false;
    }
}
