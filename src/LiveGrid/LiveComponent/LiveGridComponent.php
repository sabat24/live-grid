<?php

namespace App\LiveGrid\LiveComponent;

use App\LiveGrid\Model\LiveGridView;
use App\LiveGrid\Port\LiveGridConfigurationRegistryInterface;
use App\LiveGrid\Service\LiveGridFactory;
use App\Queryable\Attribute\QueryableProp;
use App\Queryable\LiveComponent\QueryableComponentTrait;
use App\Queryable\Service\QueryableParamsBuilder;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent('live_grid', template: '@LiveGrid/live_grid.html.twig', route: 'live_component_admin')]
final class LiveGridComponent
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use QueryableComponentTrait;

    #[LiveProp]
    public string $name = '';

    #[LiveProp(writable: true)]
    #[QueryableProp]
    public int $page = 1;

    #[LiveProp(writable: true)]
    #[QueryableProp]
    public int $resultsPerPage = 10;

    private ?LiveGridView $view = null;

    public function __construct(
        private readonly LiveGridFactory $liveGridFactory,
        private readonly LiveGridConfigurationRegistryInterface $configurationRegistry,
        private readonly FormFactoryInterface $formFactory,
        private readonly RequestStack $requestStack,
        QueryableParamsBuilder $queryableParamsBuilder,
    ) {
        $this->generateUniqueComponentName = true;
        $this->setQueryableParamsBuilder($queryableParamsBuilder);
    }

    protected function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    #[PostMount]
    public function onMountedEvent(array $data): array
    {
        $this->refreshView();

        return $data;
    }

    #[PreReRender]
    public function onPreRenderEvent(): void
    {
        $this->refreshView();
        $this->updateQueryString();
    }

    private function refreshView(): void
    {
        if ($this->name === '') {
            throw new \LogicException('LiveGridComponent requires a non-empty "name" LiveProp matching a configured live_grid.grids entry.');
        }

        $filterForm = null;
        $configuration = $this->configurationRegistry->get($this->name);
        if ($configuration->filterForm !== null) {
            $filterForm = $this->getSearchFormInstance();
        }

        $this->view = $this->liveGridFactory->createView(
            $this->name,
            $this->page,
            $this->resultsPerPage,
            $filterForm,
        );
        $this->page = $this->view->page->currentPage;
        $this->resultsPerPage = $this->view->page->maxPerPage;
    }

    public function getView(): LiveGridView
    {
        if ($this->view === null) {
            $this->refreshView();
        }

        /** @var LiveGridView $view */
        $view = $this->view;

        return $view;
    }

    /**
     * @return iterable<int, mixed>
     */
    public function getResources(): iterable
    {
        return $this->getView()->resources();
    }

    public function getResourcesCount(): int
    {
        return $this->getView()->resourcesCount();
    }

    /**
     * @return list<int>
     */
    public function getAllowedPaginate(): array
    {
        return $this->getView()->definition->allowedPaginate;
    }

    /**
     * @return FormInterface<mixed>
     */
    public function getSearchFormInstance(): FormInterface
    {
        $form = $this->instantiateForm();
        if ($this->formValues !== []) {
            $form->submit($this->formValues);
        }

        return $form;
    }

    #[LiveAction]
    public function updatePage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    #[LiveAction]
    public function updateResultsPerPage(#[LiveArg] int $resultsPerPage): void
    {
        $this->resultsPerPage = $resultsPerPage;
    }

    #[LiveAction]
    public function search(): void
    {
        $this->submitForm();
        $this->page = 1;
    }

    /**
     * @return FormInterface<mixed>
     */
    protected function instantiateForm(): FormInterface
    {
        $configuration = $this->configurationRegistry->get($this->name);
        $formType = $configuration->filterForm ?? FormType::class;
        if (!is_a($formType, \Symfony\Component\Form\FormTypeInterface::class, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Live grid "%s" filter_form "%s" must implement FormTypeInterface.',
                $this->name,
                $formType,
            ));
        }

        return $this->formFactory->create($formType);
    }

    // Overrides ComponentWithFormTrait::getDataModelValue() — prevents re-render on every field change.
    private function getDataModelValue(): string
    {
        return 'norender|*';
    }

    /**
     * @param array<mixed, mixed> $params
     */
    protected function applyQueryableFormParams(array $params): void
    {
        $configuration = $this->configurationRegistry->get($this->name);
        if ($configuration->filterForm === null) {
            return;
        }

        $formName = $this->instantiateForm()->getName();
        if (isset($params[$formName]) && is_array($params[$formName])) {
            $this->formValues = $params[$formName];
            $this->submitForm();
        }
    }

    protected function resolveQueryableFormView(): FormView
    {
        $configuration = $this->configurationRegistry->get($this->name);
        if ($configuration->filterForm === null) {
            return $this->formFactory->create(FormType::class)->createView();
        }

        return $this->getFormView();
    }

    protected function resetQueryableFormView(): void
    {
        $this->formView = null;
    }

    public function hasFilterForm(): bool
    {
        return $this->configurationRegistry->get($this->name)->filterForm !== null;
    }

    /**
     * Hook namespace helpers for templates.
     */
    public function hook(string $section): string
    {
        return sprintf('live_grid.%s.%s', $this->name, $section);
    }
}
