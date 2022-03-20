<?php

declare(strict_types=1);

namespace App\Bundles\CurrencyBundle\Twig;

use App\Bundles\CurrencyBundle\Templating\Helper\CurrencyHelperInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class CurrencyExtension extends AbstractExtension
{
    public function __construct(private readonly CurrencyHelperInterface $helper)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sylius_currency_symbol', [$this->helper, 'convertCurrencyCodeToSymbol']),
        ];
    }
}
