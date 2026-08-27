<?php

namespace App\LiveGrid\Event;

use App\LiveGrid\Model\LiveGridConfiguration;
use Symfony\Contracts\EventDispatcher\Event;

final class LiveGridInitializeEvent extends Event
{
    public function __construct(
        private LiveGridConfiguration $configuration,
    ) {
    }

    public function getConfiguration(): LiveGridConfiguration
    {
        return $this->configuration;
    }

    public function setConfiguration(LiveGridConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }
}
