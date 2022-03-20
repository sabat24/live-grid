<?php

namespace App\Component\LiveComponent\Trait;

use App\Component\LiveComponent\Attribute\QueryableProp;
use App\Component\LiveComponent\Attribute\QueryablePropContext;
use Sylius\Component\Resource\Reflection\ClassReflection;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PostHydrate;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\Twig\DeterministicTwigIdCalculator;
use Symfony\UX\TwigComponent\Attribute\PostMount;

trait QueryableComponentTrait
{
    #[LiveProp(writable: true)]
    public ?string $queryString = null;
    #[LiveProp(writable: true)]
    public ?string $windowQueryString = null;
    #[LiveProp]
    public string $componentName;

    // set to true in your component class if you are going to use two or more components on same page
    private bool $generateUniqueComponentName = false;

    /**
     * @var array{string: QueryablePropContext} $queryableFrontendPropertyNames
     */
    private array $queryableFrontendPropertyNames = [];

    public static function queryableProps(object $component): \Traversable
    {
        $properties = [];

        foreach (self::propertiesFor($component) as $property) {
            if (!$attribute = $property->getAttributes(QueryableProp::class)[0] ?? null) {
                continue;
            }

            if (\in_array($property->getName(), $properties, true)) {
                // property name was already used
                continue;
            }

            $properties[] = $property->getName();

            yield new QueryablePropContext($attribute->newInstance(), $property);
        }
    }

    /**
     * @param object $object
     * @return \Traversable
     */
    private static function propertiesFor(object $object): \Traversable
    {
        $class = $object instanceof \ReflectionClass ? $object : new \ReflectionClass($object);

        foreach ($class->getProperties() as $property) {
            yield $property;
        }

        if ($parent = $class->getParentClass()) {
            yield from self::propertiesFor($parent);
        }
    }

    private function initializeQueryable(): void
    {
        foreach (self::queryableProps($this) as $context) {
            $property = $context->reflectionProperty();
            $queryableProp = $context->queryableProp();
            $name = $property->getName();
            $frontendName = $queryableProp->calculateFieldName($this, $property->getName());

            if (isset($this->queryableFrontendPropertyNames[$frontendName])) {
                $message = sprintf(
                    'The field name "%s" cannot be used by multiple QueryableProp properties in a component. Currently, both "%s" and "%s" are trying to use it in "%s".',
                    $frontendName,
                    $this->queryableFrontendPropertyNames[$frontendName],
                    $name,
                    \get_class($component),
                );

                if ($frontendName === $this->queryableFrontendPropertyNames[$frontendName] || $frontendName === $name) {
                    $message .= sprintf(
                        ' Try adding QueryableProp(fieldName="somethingElse") for the "%s" property to avoid this.',
                        $frontendName,
                    );
                }

                throw new \LogicException($message);
            }

            $this->queryableFrontendPropertyNames[$frontendName] = $context;
        }
    }

    // we need to set priority higher than initializeForm in ComponentWithFormTrait to set initial values from query
    #[PostMount(1)]
    public function updatePropsFromRequest(array $data): array
    {
        $this->initializeQueryable();
        $this->componentName = $this->generateComponentName();
        if ($this->generateUniqueComponentName && !isset($data['data-live-id'])) {
            $data['data-live-id'] = $this->componentName;
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request->query->has($this->componentName)) {
            $this->updateProps($request->get($this->componentName));
        }

        return $data;
    }

    #[PostHydrate]
    public function updatePropsAfterHistoryChanges(): void
    {
        $this->initializeQueryable();
        if ($this->windowQueryString !== null) {
            parse_str($this->windowQueryString, $queryParams);

            if (isset($queryParams[$this->componentName])) {
                $this->updateProps($queryParams[$this->componentName]);
            } else {
                $this->resetPropsToDefaultValues(array_keys($this->queryableFrontendPropertyNames));
            }
        }
    }

    private function resetPropsToDefaultValues(array $frontendPropertyNames): void
    {
        $propertyAccessor = new PropertyAccessor();
        foreach ($frontendPropertyNames as $frontendPropertyName) {
            if (!isset($this->queryableFrontendPropertyNames[$frontendPropertyName])) {
                continue;
            }

            $queryablePropContext = $this->queryableFrontendPropertyNames[$frontendPropertyName];
            $reflectionProperty = $queryablePropContext->reflectionProperty();
            if (
                $queryablePropContext->queryableProp()->isReadonly()
                || !$reflectionProperty->hasDefaultValue()
            ) {
                continue;
            }

            $propertyAccessor->setValue($this, $reflectionProperty->getName(), $reflectionProperty->getDefaultValue());
        }
    }

    private function updateProps(array $params): void
    {
        if ($this->hasFormTrait()) {
            $form = $this->getFormInstance();
            if (isset($params[$form->getName()])) {
                $this->formValues = $params[$form->getName()];
                $this->submitForm();
            }
        }
        $propertyAccessor = new PropertyAccessor();
        $frontendPropertyNamesToReset = [];
        /** @var QueryablePropContext $queryablePropContext */
        foreach ($this->queryableFrontendPropertyNames as $frontendPropertyName => $queryablePropContext) {
            if ($queryablePropContext->queryableProp()->isReadonly()) {
                continue;
            }

            if (!isset($params[$frontendPropertyName])) {
                $frontendPropertyNamesToReset[] = $frontendPropertyName;
                continue;
            }

            $propertyAccessor->setValue(
                $this,
                $queryablePropContext->reflectionProperty()->getName(),
                $params[$frontendPropertyName],
            );
        }

        $this->resetPropsToDefaultValues($frontendPropertyNamesToReset);
    }

    private function updateQueryString(): void
    {
        $params = [];

        if ($this->hasFormTrait()) {
            $form = $this->getForm();
            foreach (new \IteratorIterator($form) as $item) {
                if (!empty($item->vars['value']) || $item->vars['value'] == 0) {
                    parse_str($item->vars['full_name'] . '=' . $item->vars['value'], $queryStringParams);
                    $params = array_merge_recursive($params, $queryStringParams);
                }
            }
            $this->formView = null;
        }

        $propertyAccessor = new PropertyAccessor();

        /** @var QueryablePropContext $queryablePropContext */
        foreach ($this->queryableFrontendPropertyNames as $frontendPropertyName => $queryablePropContext) {
            $property = $queryablePropContext->reflectionProperty();
            $propertyValue = $propertyAccessor->getValue($this, $property->getName());
            if ($property->hasDefaultValue() && $property->getDefaultValue() === $propertyValue) {
                continue;
            }

            $params[$frontendPropertyName] = $propertyValue;
        }

        if (!empty($params)) {
            $this->queryString = http_build_query([$this->componentName => $params]);
        } else {
            $this->queryString = '';
        }
    }

    private function classUsesDeep(string $class, bool $autoload = true): array
    {
        $traits = class_uses($class, $autoload);

        if ($parent = get_parent_class($class)) {
            $traits = array_merge($traits, $this->classUsesDeep($parent, $autoload));
        }

        foreach ($traits as $trait) {
            $traits = array_merge($traits, $this->classUsesDeep($trait, $autoload));
        }

        return $traits;
    }

    public function generateComponentName(): string
    {
        return $this->generateUniqueComponentName
            ? $this->getUniqueComponentName()
            : $this->getAttributeBasedComponentName();
    }

    private function getUniqueComponentName(): string
    {
        return (new DeterministicTwigIdCalculator())->calculateDeterministicId();
    }

    private function getAttributeBasedComponentName(): string
    {
        /** @var \ReflectionAttribute $attribute */
        $attribute = current(ClassReflection::getClassAttributes(self::class, AsLiveComponent::class));

        return $attribute->getArguments()[0];
    }

    private function hasFormTrait(): bool
    {
        return in_array(ComponentWithFormTrait::class, $this->classUsesDeep(self::class));
    }
}
