<?php

declare(strict_types=1);

namespace App\Bundles\CurrencyBundle\Templating\Helper;

use Symfony\Component\Intl\Currencies;

class CurrencyHelper implements CurrencyHelperInterface
{
    public function convertCurrencyCodeToSymbol(string $code): string
    {
        return Currencies::getSymbol($code);
    }
}
