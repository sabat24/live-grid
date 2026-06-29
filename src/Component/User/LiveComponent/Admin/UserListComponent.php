<?php

namespace App\Component\User\LiveComponent\Admin;

use App\Component\Grid\Model\GridComponentInterface;
use App\Component\Grid\Service\GridLiveComponentService;
use App\Component\LiveComponent\Attribute\QueryableProp;
use App\Component\LiveComponent\Service\QueryableParamsBuilder;
use App\Component\LiveComponent\Trait\QueryableComponentTrait;
use App\Component\User\Entity\User;
use App\Component\User\Form\FilterType\UserListFilterType;
use App\Component\User\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Grid\View\GridView;
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

#[AsLiveComponent('admin:user_list', route: 'live_component_admin')]
final class UserListComponent implements GridComponentInterface
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use QueryableComponentTrait;

    #[LiveProp(writable: true)]
    #[QueryableProp]
    public int $page = 1;
    #[LiveProp(writable: true)]
    #[QueryableProp]
    public int $resultsPerPage = 10;

    /** @var Pagerfanta<User> */
    private Pagerfanta $paginator;

    private const GRID_NAME = 'app_admin_user';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FormFactoryInterface $formFactory,
        private readonly RequestStack $requestStack,
        private readonly GridLiveComponentService $gridLiveComponentService,
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
        $this->gridLiveComponentService->initialize($this, self::GRID_NAME);

        $this->paginator = $this->gridLiveComponentService->createPaginator($this->page, $this->resultsPerPage);
        $this->page = $this->paginator->getCurrentPage();

        return $data;
    }

    #[PreReRender]
    public function onPreRenderEvent(): void
    {
        $this->gridLiveComponentService->initialize($this, self::GRID_NAME);
        $this->paginator = $this->gridLiveComponentService->createPaginator($this->page, $this->resultsPerPage);
        $this->page = $this->paginator->getCurrentPage();
        $this->updateQueryString();
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
    }

    /**
     * @return iterable<int, User>
     */
    public function getResources(): iterable
    {
        return $this->paginator->getCurrentPageResults();
    }

    public function getResourcesCount(): int
    {
        return $this->paginator->getNbResults();
    }

    /**
     * @return FormInterface<mixed>
     */
    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create(UserListFilterType::class);
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
        $formName = $this->instantiateForm()->getName();
        if (isset($params[$formName]) && is_array($params[$formName])) {
            $this->formValues = $params[$formName];
            $this->submitForm();
        }
    }

    protected function resolveQueryableFormView(): FormView
    {
        return $this->getFormView();
    }

    protected function resetQueryableFormView(): void
    {
        $this->formView = null;
    }

    public function createFilterQueryBuilder(): QueryBuilder
    {
        return $this->userRepository->createQueryBuilder('u');
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return [
            'filterable' => true,
            'default_page_size' => $this->resultsPerPage,
            'section' => 'admin',
        ];
    }

    /**
     * @return list<int>
     */
    public function getAllowedPaginate(): array
    {
        return $this->gridLiveComponentService->getAllowedPaginate();
    }

    public function getGrid(): GridView
    {
        return $this->gridLiveComponentService->getGridView();
    }
}
