<?php

namespace App\Adapter\Sylius;

use App\Adapter\Sylius\DependencyInjection\SyliusLiveGridExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SyliusLiveGridAdapterBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new SyliusLiveGridExtension();
    }
}
