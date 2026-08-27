<?php

namespace App\LiveGrid\Renderer;

use App\LiveGrid\Model\FieldDefinition;
use App\LiveGrid\Model\LiveGridView;
use App\LiveGrid\Port\FieldRendererInterface;

final class CompositeFieldRenderer implements FieldRendererInterface
{
    /**
     * @param iterable<FieldRendererInterface> $renderers
     */
    public function __construct(
        private readonly iterable $renderers,
    ) {
    }

    public function supports(FieldDefinition $field, LiveGridView $view): bool
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($field, $view)) {
                return true;
            }
        }

        return false;
    }

    public function render(FieldDefinition $field, LiveGridView $view, mixed $row): string
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($field, $view)) {
                return $renderer->render($field, $view, $row);
            }
        }

        throw new \LogicException(sprintf('No field renderer supports field "%s".', $field->name));
    }
}
