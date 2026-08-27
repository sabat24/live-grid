<?php

namespace App\LiveGrid\Port;

use App\LiveGrid\Model\FieldDefinition;
use App\LiveGrid\Model\LiveGridView;

interface FieldRendererInterface
{
    public function supports(FieldDefinition $field, LiveGridView $view): bool;

    public function render(FieldDefinition $field, LiveGridView $view, mixed $row): string;
}
