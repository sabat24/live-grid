<?php

namespace App\Component\LiveComponent\Service;

use App\Component\LiveComponent\Attribute\QueryablePropContext;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class QueryableParamsBuilder
{
    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    /**
     * @param array<string, QueryablePropContext> $queryablePropContexts
     *
     * @return array<string, mixed>
     */
    public function buildParams(object $component, array $queryablePropContexts, ?FormView $formView = null): array
    {
        /** @var array<string, mixed> $params */
        $params = [];

        if ($formView !== null) {
            foreach (new \IteratorIterator($formView) as $item) {
                /** @var array<string, mixed> $vars */
                $vars = $item->vars;
                $value = $vars['value'] ?? null;
                if (!$this->shouldIncludeFormValue($value)) {
                    continue;
                }

                $fullName = $vars['full_name'] ?? '';
                if (!is_string($fullName) || $fullName === '') {
                    continue;
                }

                if (!is_scalar($value) && $value !== null) {
                    continue;
                }

                parse_str($fullName . '=' . (string) $value, $queryStringParams);
                $params = array_merge_recursive($params, $queryStringParams);
            }
        }

        foreach ($queryablePropContexts as $frontendPropertyName => $queryablePropContext) {
            $property = $queryablePropContext->reflectionProperty();
            $propertyValue = $this->propertyAccessor->getValue($component, $property->getName());
            if ($property->hasDefaultValue() && $property->getDefaultValue() === $propertyValue) {
                continue;
            }

            $params[$frontendPropertyName] = $propertyValue;
        }

        return $this->normalizeParams($params);
    }

    /**
     * @param array<int|string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function normalizeParams(array $params): array
    {
        $normalized = [];

        foreach ($params as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * @param array<string, QueryablePropContext> $queryablePropContexts
     */
    public function buildQueryString(string $componentName, object $component, array $queryablePropContexts, ?FormView $formView = null): string
    {
        $params = $this->buildParams($component, $queryablePropContexts, $formView);

        if ($params === []) {
            return '';
        }

        return http_build_query([$componentName => $params]);
    }

    /**
     * @param array<mixed, mixed> $params
     * @param array<string, QueryablePropContext> $queryablePropContexts
     */
    public function applyParams(object $component, array $params, array $queryablePropContexts): void
    {
        $frontendPropertyNamesToReset = [];

        foreach ($queryablePropContexts as $frontendPropertyName => $queryablePropContext) {
            if ($queryablePropContext->queryableProp()->isReadonly()) {
                continue;
            }

            if (!array_key_exists($frontendPropertyName, $params)) {
                $frontendPropertyNamesToReset[] = $frontendPropertyName;
                continue;
            }

            $this->propertyAccessor->setValue(
                $component,
                $queryablePropContext->reflectionProperty()->getName(),
                $params[$frontendPropertyName],
            );
        }

        $this->resetToDefaults($component, $frontendPropertyNamesToReset, $queryablePropContexts);
    }

    /**
     * @param list<string> $frontendPropertyNames
     * @param array<string, QueryablePropContext> $queryablePropContexts
     */
    public function resetToDefaults(object $component, array $frontendPropertyNames, array $queryablePropContexts): void
    {
        foreach ($frontendPropertyNames as $frontendPropertyName) {
            if (!isset($queryablePropContexts[$frontendPropertyName])) {
                continue;
            }

            $queryablePropContext = $queryablePropContexts[$frontendPropertyName];
            $reflectionProperty = $queryablePropContext->reflectionProperty();
            if (
                $queryablePropContext->queryableProp()->isReadonly()
                || !$reflectionProperty->hasDefaultValue()
            ) {
                continue;
            }

            $this->propertyAccessor->setValue(
                $component,
                $reflectionProperty->getName(),
                $reflectionProperty->getDefaultValue(),
            );
        }
    }

    private function shouldIncludeFormValue(mixed $value): bool
    {
        if ($value === 0 || $value === '0') {
            return true;
        }

        if ($value === '' || $value === null) {
            return false;
        }

        if ($value === []) {
            return false;
        }

        return true;
    }
}
