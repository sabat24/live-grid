<?php

namespace App\LiveGrid\Renderer;

use App\LiveGrid\Model\FieldDefinition;
use App\LiveGrid\Model\LiveGridView;
use App\LiveGrid\Port\FieldRendererInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Twig\Environment;

/**
 * Generic string/property path renderer for non-Sylius grids.
 */
final class PropertyPathFieldRenderer implements FieldRendererInterface
{
    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly Environment $twig,
    ) {
    }

    public function supports(FieldDefinition $field, LiveGridView $view): bool
    {
        return true;
    }

    public function render(FieldDefinition $field, LiveGridView $view, mixed $row): string
    {
        if ($field->type === 'twig') {
            $template = $field->options['template'] ?? null;
            if (is_string($template) && $template !== '') {
                return $this->twig->render($template, [
                    'data' => $row,
                    'field' => $field,
                    'grid' => $view,
                    'options' => $field->options,
                ]);
            }
        }

        if (!is_array($row) && !is_object($row)) {
            return '';
        }

        $value = $this->propertyAccessor->getValue($row, $field->path);
        if ($value === null) {
            return '';
        }

        if (is_scalar($value) || $value instanceof \Stringable) {
            return htmlspecialchars((string) $value, \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
        }

        return '';
    }
}
