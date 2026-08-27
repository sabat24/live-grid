<?php

namespace App\LiveGrid;

use App\LiveGrid\DependencyInjection\LiveGridExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LiveGridBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new LiveGridExtension();
    }
}
