<?php

declare(strict_types=1);

namespace App\Bundles\CurrencyBundle\Templating\Helper;

use Symfony\Component\Intl\Currencies;
use Symfony\Component\Templating\Helper\Helper;

class CurrencyHelper extends Helper implements CurrencyHelperInterface
{
    public function convertCurrencyCodeToSymbol(string $code): string
    {
        return Currencies::getSymbol($code);
    }

    public function getName(): string
    {
        return 'sylius_currency';
    }
}
