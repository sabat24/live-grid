<?php

namespace App\LiveGrid\Event;

use App\LiveGrid\Model\LiveGridConfiguration;
use App\LiveGrid\Model\LiveGridView;
use Symfony\Contracts\EventDispatcher\Event;

final class LiveGridViewEvent extends Event
{
    public function __construct(
        private readonly LiveGridConfiguration $configuration,
        private LiveGridView $view,
    ) {
    }

    public function getConfiguration(): LiveGridConfiguration
    {
        return $this->configuration;
    }

    public function getView(): LiveGridView
    {
        return $this->view;
    }

    public function setView(LiveGridView $view): void
    {
        $this->view = $view;
    }
}
