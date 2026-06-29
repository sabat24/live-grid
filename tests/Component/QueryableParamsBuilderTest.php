<?php

namespace App\Tests\Component;

use App\Component\LiveComponent\Attribute\QueryableProp;
use App\Component\LiveComponent\Attribute\QueryablePropContext;
use App\Component\LiveComponent\Service\QueryableParamsBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Forms;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class QueryableParamsBuilderTest extends TestCase
{
    private QueryableParamsBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new QueryableParamsBuilder(new PropertyAccessor());
    }

    public function testBuildQueryStringReturnsEmptyForDefaultsOnly(): void
    {
        $subject = new QueryableParamsSubject();
        $contexts = $this->contextsFor($subject);

        self::assertSame('', $this->builder->buildQueryString('test_component', $subject, $contexts));
    }

    public function testBuildQueryStringIncludesNonDefaultPage(): void
    {
        $subject = new QueryableParamsSubject();
        $subject->page = 2;
        $contexts = $this->contextsFor($subject);

        $queryString = $this->builder->buildQueryString('test_component', $subject, $contexts);

        $params = $this->parseComponentParams($queryString);
        self::assertSame('2', $params['page']);
        self::assertArrayNotHasKey('resultsPerPage', $this->parseComponentParams($queryString));
    }

    public function testBuildQueryStringIncludesNonDefaultResultsPerPage(): void
    {
        $subject = new QueryableParamsSubject();
        $subject->resultsPerPage = 25;
        $contexts = $this->contextsFor($subject);

        $queryString = $this->builder->buildQueryString('test_component', $subject, $contexts);

        $params = $this->parseComponentParams($queryString);
        self::assertSame('25', $params['resultsPerPage']);
        self::assertArrayNotHasKey('page', $this->parseComponentParams($queryString));
    }

    public function testBuildQueryStringOmitsEmptyFormFields(): void
    {
        $subject = new QueryableParamsSubject();
        $contexts = $this->contextsFor($subject);
        $formView = $this->createFormView(['search' => '']);

        self::assertSame('', $this->builder->buildQueryString('test_component', $subject, $contexts, $formView));
    }

    public function testBuildQueryStringIncludesNonEmptyFormFields(): void
    {
        $subject = new QueryableParamsSubject();
        $contexts = $this->contextsFor($subject);
        $formView = $this->createFormView(['search' => 'admin']);

        $queryString = $this->builder->buildQueryString('test_component', $subject, $contexts, $formView);

        $params = $this->parseComponentParams($queryString);
        $formParams = $params['form'] ?? null;
        self::assertIsArray($formParams);
        self::assertSame('admin', $formParams['search']);
    }

    public function testApplyParamsSetsPropertyFromParams(): void
    {
        $subject = new QueryableParamsSubject();
        $contexts = $this->contextsFor($subject);

        $this->builder->applyParams($subject, ['page' => 3], $contexts);

        self::assertSame(3, $subject->page);
    }

    public function testApplyParamsResetsMissingKeysToDefaults(): void
    {
        $subject = new QueryableParamsSubject();
        $subject->page = 3;
        $subject->resultsPerPage = 25;
        $contexts = $this->contextsFor($subject);

        $this->builder->applyParams($subject, [], $contexts);

        self::assertSame(1, $subject->page);
        self::assertSame(10, $subject->resultsPerPage);
    }

    public function testResetToDefaultsResetsSpecifiedProperties(): void
    {
        $subject = new QueryableParamsSubject();
        $subject->page = 3;
        $subject->resultsPerPage = 25;
        $contexts = $this->contextsFor($subject);

        $this->builder->resetToDefaults($subject, ['page', 'resultsPerPage'], $contexts);

        self::assertSame(1, $subject->page);
        self::assertSame(10, $subject->resultsPerPage);
    }

    /**
     * @return array<string, QueryablePropContext>
     */
    private function contextsFor(QueryableParamsSubject $subject): array
    {
        $contexts = [];
        $reflection = new \ReflectionClass($subject);
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(QueryableProp::class);
            if ($attributes === []) {
                continue;
            }

            $context = new QueryablePropContext($attributes[0]->newInstance(), $property);
            $frontendName = $context->queryableProp()->calculateFieldName($subject, $property->getName());
            $contexts[$frontendName] = $context;
        }

        return $contexts;
    }

    /**
     * @param array<string, string> $data
     */
    private function createFormView(array $data): FormView
    {
        $formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $form = $formFactory->createBuilder()
            ->setAction('')
            ->setMethod('GET')
            ->add('search', TextType::class)
            ->getForm()
        ;
        $form->submit($data);

        return $form->createView();
    }

    /**
     * @return array<string, mixed>
     */
    private function parseComponentParams(string $queryString): array
    {
        parse_str(urldecode($queryString), $parsed);

        $componentParams = $parsed['test_component'] ?? [];
        if (!is_array($componentParams)) {
            return [];
        }

        /** @var array<string, mixed> $componentParams */
        return $componentParams;
    }
}

final class QueryableParamsSubject
{
    #[QueryableProp]
    public int $page = 1;

    #[QueryableProp]
    public int $resultsPerPage = 10;
}
