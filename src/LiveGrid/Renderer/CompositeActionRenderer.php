<?php

namespace App\LiveGrid\Renderer;

use App\LiveGrid\Model\ActionDefinition;
use App\LiveGrid\Model\LiveGridView;
use App\LiveGrid\Port\ActionRendererInterface;

final class CompositeActionRenderer implements ActionRendererInterface
{
    /**
     * @param iterable<ActionRendererInterface> $renderers
     */
    public function __construct(
        private readonly iterable $renderers,
    ) {
    }

    public function supports(ActionDefinition $action, LiveGridView $view): bool
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($action, $view)) {
                return true;
            }
        }

        return false;
    }

    public function render(ActionDefinition $action, LiveGridView $view, mixed $row = null): string
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($action, $view)) {
                return $renderer->render($action, $view, $row);
            }
        }

        throw new \LogicException(sprintf('No action renderer supports action "%s".', $action->name));
    }
}
