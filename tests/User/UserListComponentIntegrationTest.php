<?php

namespace App\Tests\User;

use App\Component\Grid\Service\GridLiveComponentService;
use App\Component\LiveComponent\Service\QueryableParamsBuilder;
use App\Component\User\Entity\User;
use App\Component\User\LiveComponent\Admin\UserListComponent;
use App\Component\User\Repository\UserRepository;
use App\Tests\ContainerTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class UserListComponentIntegrationTest extends KernelTestCase
{
    use ContainerTestTrait;

    public function testDefaultPaginationShowsTwentySixUsersAcrossThreePages(): void
    {
        $component = $this->createUserListComponent();

        self::assertSame(26, $component->getResourcesCount());
        self::assertCount(10, $this->resourcesToArray($component->getResources()));
    }

    public function testPageTwoReturnsTenUsers(): void
    {
        $component = $this->createUserListComponent(page: 2);

        self::assertSame(2, $component->page);
        self::assertCount(10, $this->resourcesToArray($component->getResources()));
    }

    public function testPageThreeReturnsRemainingSixUsers(): void
    {
        $component = $this->createUserListComponent(page: 3);

        self::assertSame(3, $component->page);
        self::assertCount(6, $this->resourcesToArray($component->getResources()));
    }

    public function testEmailFilterReturnsSingleAdminUser(): void
    {
        $component = $this->createUserListComponent(formValues: [
            'email' => 'admin@live-grid.com',
        ]);

        self::assertSame(1, $component->getResourcesCount());
        $resources = $this->resourcesToArray($component->getResources());
        self::assertSame('admin@live-grid.com', $resources[0]->getEmail());
    }

    public function testFilterFromPageThreeClampsToLastValidPage(): void
    {
        $component = $this->createUserListComponent(
            page: 3,
            formValues: [
                'email' => 'admin@live-grid.com',
            ],
        );

        self::assertSame(1, $component->page);
        self::assertSame(1, $component->getResourcesCount());
    }

    public function testQueryStringOmitsDefaultPageAfterFilter(): void
    {
        $component = $this->createUserListComponent(formValues: [
            'email' => 'admin@live-grid.com',
        ]);

        $queryString = $component->queryString ?? '';
        self::assertStringContainsString('admin%40live-grid.com', $queryString);
        self::assertStringNotContainsString('page=', $queryString);
    }

    /**
     * @param array<string, mixed>|null $formValues
     */
    private function createUserListComponent(int $page = 1, int $resultsPerPage = 10, ?array $formValues = null): UserListComponent
    {
        $container = $this->getTestContainer();

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/admin/users/'));

        $component = new UserListComponent(
            $container->get(UserRepository::class),
            $container->get(FormFactoryInterface::class),
            $requestStack,
            $container->get(GridLiveComponentService::class),
            $container->get(QueryableParamsBuilder::class),
        );

        $this->disableUniqueComponentName($component);
        $component->updatePropsFromRequest([]);
        $component->page = $page;
        $component->resultsPerPage = $resultsPerPage;
        $component->onMountedEvent([]);

        if (null !== $formValues) {
            $component->formValues = $formValues;
            $component->search();
        }

        $component->onPreRenderEvent();

        return $component;
    }

    /**
     * @param iterable<int, User> $resources
     *
     * @return list<User>
     */
    private function resourcesToArray(iterable $resources): array
    {
        return [...$resources];
    }

    private function disableUniqueComponentName(UserListComponent $component): void
    {
        $reflection = new \ReflectionProperty($component, 'generateUniqueComponentName');
        $reflection->setAccessible(true);
        $reflection->setValue($component, false);
    }
}
