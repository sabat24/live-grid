<?php

namespace App\Component\LiveComponent\Trait;

use App\Component\LiveComponent\Attribute\QueryableProp;
use App\Component\LiveComponent\Attribute\QueryablePropContext;
use App\Component\LiveComponent\Service\QueryableParamsBuilder;
use Sylius\Component\Resource\Reflection\ClassReflection;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PostHydrate;
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

    private QueryableParamsBuilder $queryableParamsBuilder;

    /**
     * @var array<string, QueryablePropContext>
     */
    private array $queryableFrontendPropertyNames = [];

    abstract protected function getRequestStack(): RequestStack;

    public function setQueryableParamsBuilder(QueryableParamsBuilder $queryableParamsBuilder): void
    {
        $this->queryableParamsBuilder = $queryableParamsBuilder;
    }

    /**
     * @return \Traversable<int, QueryablePropContext>
     */
    public static function queryableProps(object $component): \Traversable
    {
        $properties = [];

        foreach (self::propertiesFor($component) as $property) {
            $attributes = $property->getAttributes(QueryableProp::class);
            if ($attributes === []) {
                continue;
            }

            if (\in_array($property->getName(), $properties, true)) {
                continue;
            }

            $properties[] = $property->getName();

            yield new QueryablePropContext($attributes[0]->newInstance(), $property);
        }
    }

    /**
     * @return \Traversable<int, \ReflectionProperty>
     */
    private static function propertiesFor(object $object): \Traversable
    {
        $class = $object instanceof \ReflectionClass ? $object : new \ReflectionClass($object);

        foreach ($class->getProperties() as $property) {
            yield $property;
        }

        $parent = $class->getParentClass();
        if ($parent !== false) {
            yield from self::propertiesFor($parent);
        }
    }

    private function initializeQueryable(): void
    {
        $this->queryableFrontendPropertyNames = [];

        foreach (self::queryableProps($this) as $context) {
            $property = $context->reflectionProperty();
            $queryableProp = $context->queryableProp();
            $name = $property->getName();
            $frontendName = $queryableProp->calculateFieldName($this, $property->getName());

            if (isset($this->queryableFrontendPropertyNames[$frontendName])) {
                $existingContext = $this->queryableFrontendPropertyNames[$frontendName];
                $message = sprintf(
                    'The field name "%s" cannot be used by multiple QueryableProp properties in a component. Currently, both "%s" and "%s" are trying to use it in "%s".',
                    $frontendName,
                    $existingContext->reflectionProperty()->getName(),
                    $name,
                    \get_class($this),
                );

                if ($frontendName === $existingContext->reflectionProperty()->getName() || $frontendName === $name) {
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

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    // we need to set priority higher than initializeForm in ComponentWithFormTrait to set initial values from query
    #[PostMount(1)]
    public function updatePropsFromRequest(array $data): array
    {
        $this->initializeQueryable();
        $this->componentName = $this->generateComponentName();
        if ($this->generateUniqueComponentName && !isset($data['data-live-id'])) {
            $data['data-live-id'] = $this->componentName;
        }

        $request = $this->getRequestStack()->getCurrentRequest();
        if ($request instanceof Request && $request->query->has($this->componentName)) {
            $componentParams = $request->query->all()[$this->componentName] ?? null;
            if (is_array($componentParams)) {
                $this->updateProps($componentParams);
            }
        }

        return $data;
    }

    #[PostHydrate]
    public function updatePropsAfterHistoryChanges(): void
    {
        $this->initializeQueryable();
        if ($this->windowQueryString !== null) {
            parse_str($this->windowQueryString, $queryParams);

            if (isset($queryParams[$this->componentName]) && is_array($queryParams[$this->componentName])) {
                $this->updateProps($queryParams[$this->componentName]);
            } else {
                $this->queryableParamsBuilder->resetToDefaults(
                    $this,
                    array_keys($this->queryableFrontendPropertyNames),
                    $this->queryableFrontendPropertyNames,
                );
            }
        }
    }

    /**
     * @param array<mixed, mixed> $params
     */
    private function updateProps(array $params): void
    {
        $this->applyQueryableFormParams($params);
        $this->queryableParamsBuilder->applyParams($this, $params, $this->queryableFrontendPropertyNames);
    }

    /**
     * Override in components that use ComponentWithFormTrait to hydrate the form from query params.
     *
     * @param array<mixed, mixed> $params
     */
    protected function applyQueryableFormParams(array $params): void
    {
    }

    private function updateQueryString(): void
    {
        $formView = $this->resolveQueryableFormView();

        $this->queryString = $this->queryableParamsBuilder->buildQueryString(
            $this->componentName,
            $this,
            $this->queryableFrontendPropertyNames,
            $formView,
        );

        $this->resetQueryableFormView();
    }

    protected function resolveQueryableFormView(): ?FormView
    {
        return null;
    }

    protected function resetQueryableFormView(): void
    {
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
        $attributes = ClassReflection::getClassAttributes(self::class, AsLiveComponent::class);
        $attribute = $attributes[0] ?? null;
        if ($attribute === null) {
            throw new \LogicException(
                sprintf('Live component "%s" is missing the #[AsLiveComponent] attribute.', self::class),
            );
        }

        $arguments = $attribute->getArguments();
        $name = $arguments[0] ?? $arguments['name'] ?? null;
        if (!is_string($name) || $name === '') {
            throw new \LogicException(
                sprintf('Live component "%s" has an invalid #[AsLiveComponent] name.', self::class),
            );
        }

        return $name;
    }
}
