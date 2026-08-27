<?php

namespace App\LiveGrid\Port;

use App\LiveGrid\Model\ActionDefinition;
use App\LiveGrid\Model\LiveGridView;

interface ActionRendererInterface
{
    public function supports(ActionDefinition $action, LiveGridView $view): bool;

    public function render(ActionDefinition $action, LiveGridView $view, mixed $row = null): string;
}
