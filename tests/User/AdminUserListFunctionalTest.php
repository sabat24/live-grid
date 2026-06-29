<?php

namespace App\Tests\User;

use App\Tests\AbstractWebTestCase;
use App\Tests\Component\LiveComponentSnapshot;
use App\Tests\Component\LiveComponentTestHelper;

/**
 * Server-side query-param hydration and Live actions. Browser URL sync (pushState / popstate)
 * is covered by Playwright in tests/E2e/Admin/UserListUrlSyncE2eTest.php.
 */
final class AdminUserListFunctionalTest extends AbstractWebTestCase
{
    public function testUsersIndexRequiresAdmin(): void
    {
        $client = AdminUserListFunctionalTest::createClient();
        $client->request('GET', '/admin/users/');

        self::assertNotSame(200, $client->getResponse()->getStatusCode());
    }

    public function testUsersIndexRendersTwoIndependentLists(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();

        self::assertResponseIsSuccessful();

        $components = LiveComponentTestHelper::findLiveComponents($crawler);
        self::assertCount(2, $components);

        $names = array_map(fn(LiveComponentSnapshot $c) => $c->componentName(), $components);
        self::assertSame(array_unique($names), $names);
    }

    public function testDefaultStateHasCleanQueryString(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();

        foreach (LiveComponentTestHelper::findLiveComponents($crawler) as $component) {
            self::assertSame('', $component->queryString());
        }
    }

    public function testPaginationFirstListOnly(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();
        $components = LiveComponentTestHelper::findLiveComponents($crawler);

        $crawler = LiveComponentTestHelper::callLiveAction($client, $components[0], 'updatePage', ['page' => 2]);
        $updatedFirst = LiveComponentTestHelper::findLiveComponents($crawler)[0];
        self::assertSame(2, $updatedFirst->intProp('page'));

        $client->request('GET', '/admin/users/?' . $updatedFirst->queryString());
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        $reloaded = LiveComponentTestHelper::findLiveComponents($client->getCrawler());
        $firstListName = $components[0]->componentName();
        $reloadedFirst = $this->findComponentByName($reloaded, $firstListName);
        $reloadedSecond = $this->findComponentByName(
            $reloaded,
            $components[1]->componentName(),
        );

        self::assertSame(2, $reloadedFirst->intProp('page'));
        self::assertSame(1, $reloadedSecond->intProp('page'));
    }

    public function testPaginationViaUrlQueryParam(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();
        $components = LiveComponentTestHelper::findLiveComponents($crawler);
        $componentName = $components[0]->componentName();

        $crawler = $client->request('GET', '/admin/users/', [
            $componentName => ['page' => 2],
        ]);

        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        self::assertResponseIsSuccessful();

        $reloaded = LiveComponentTestHelper::findLiveComponents($client->getCrawler());
        self::assertNotEmpty($reloaded);

        $matching = array_values(
            array_filter(
                $reloaded,
                fn (LiveComponentSnapshot $c) => $c->componentName() === $componentName,
            ),
        )[0] ?? $reloaded[0];

        self::assertSame(2, $matching->intProp('page'));
    }

    public function testFilterViaUrlQueryParam(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $components = LiveComponentTestHelper::findLiveComponents($client->getCrawler());
        $componentName = $components[0]->componentName();

        $client->request('GET', '/admin/users/', [
            $componentName => [
                'user_list_filter' => [
                    'email' => 'admin@live-grid.com',
                ],
            ],
        ]);

        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        self::assertResponseIsSuccessful();

        $reloaded = LiveComponentTestHelper::findLiveComponents($client->getCrawler());
        $matching = $this->findComponentByName($reloaded, $componentName);

        self::assertCount(1, $matching->node->filter('.flex-table-item'));
        self::assertStringContainsString('admin@live-grid.com', $matching->node->text());
    }

    public function testResultsPerPageViaUrlQueryParam(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $components = LiveComponentTestHelper::findLiveComponents($client->getCrawler());
        $componentName = $components[0]->componentName();

        $client->request('GET', '/admin/users/', [
            $componentName => ['resultsPerPage' => 50],
        ]);

        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        self::assertResponseIsSuccessful();

        $reloaded = LiveComponentTestHelper::findLiveComponents($client->getCrawler());
        $matching = $this->findComponentByName($reloaded, $componentName);

        self::assertSame(50, $matching->intProp('resultsPerPage'));
        self::assertCount(26, $matching->node->filter('.flex-table-item'));
    }

    public function testResultsPerPageInQueryString(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();
        $components = LiveComponentTestHelper::findLiveComponents($crawler);

        $crawler = LiveComponentTestHelper::callLiveAction(
            $client,
            $components[0],
            'updateResultsPerPage',
            ['resultsPerPage' => 25],
        );
        $updated = LiveComponentTestHelper::findLiveComponents($crawler)[0];

        $decodedQueryString = urldecode($updated->queryString());
        self::assertStringContainsString('[resultsPerPage]=25', $decodedQueryString);
        self::assertStringNotContainsString('[page]=', $decodedQueryString);
    }

    public function testEmailFilterNarrowsResults(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();
        $components = LiveComponentTestHelper::findLiveComponents($crawler);

        $crawler = LiveComponentTestHelper::callLiveAction(
            $client,
            $components[0],
            'search',
            [],
            [
                'user_list_filter' => [
                    'email' => 'admin@live-grid.com',
                ],
            ],
        );

        $updated = LiveComponentTestHelper::findLiveComponents($crawler)[0];
        self::assertCount(1, $updated->node->filter('.flex-table-item'));
        self::assertStringContainsString('admin@live-grid.com', $updated->node->text());
    }

    public function testFilterFromPage3ClampsToValidPage(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();
        $components = LiveComponentTestHelper::findLiveComponents($crawler);

        $crawler = LiveComponentTestHelper::callLiveAction($client, $components[0], 'updatePage', ['page' => 3]);
        $crawler = LiveComponentTestHelper::callLiveAction(
            $client,
            LiveComponentTestHelper::findLiveComponents($crawler)[0],
            'search',
            [],
            [
                'user_list_filter' => [
                    'email' => 'admin@live-grid.com',
                ],
            ],
        );

        $updated = LiveComponentTestHelper::findLiveComponents($crawler)[0];
        self::assertSame(1, $updated->intProp('page'));
        self::assertCount(1, $updated->node->filter('.flex-table-item'));
    }

    public function testBothListsCanHaveDifferentStateInUrl(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();
        $components = LiveComponentTestHelper::findLiveComponents($crawler);

        $firstList = $components[0];
        $secondList = $components[1];

        $crawler = LiveComponentTestHelper::callLiveAction($client, $firstList, 'updatePage', ['page' => 2]);
        $updatedFirst = LiveComponentTestHelper::findLiveComponents($crawler)[0];

        $crawler = LiveComponentTestHelper::callLiveAction($client, $secondList, 'updatePage', ['page' => 3]);
        $updatedSecond = LiveComponentTestHelper::findLiveComponents($crawler)[0];

        $queryString = $updatedFirst->queryString();
        if ('' !== $updatedSecond->queryString()) {
            $queryString .= '&' . $updatedSecond->queryString();
        }

        $client->request('GET', '/admin/users/?' . $queryString);
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        $updated = LiveComponentTestHelper::findLiveComponents($client->getCrawler());
        $reloadedFirst = $this->findComponentByName($updated, $firstList->componentName());
        $reloadedSecond = $this->findComponentByName($updated, $secondList->componentName());

        self::assertSame(2, $reloadedFirst->intProp('page'));
        self::assertSame(3, $reloadedSecond->intProp('page'));
    }

    public function testBothListsCanHaveDifferentFilterAndPaginationInUrl(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();
        $components = LiveComponentTestHelper::findLiveComponents($crawler);

        $firstList = $components[0];
        $secondList = $components[1];

        $crawler = LiveComponentTestHelper::callLiveAction($client, $firstList, 'updatePage', ['page' => 2]);
        $updatedFirst = LiveComponentTestHelper::findLiveComponents($crawler)[0];

        $crawler = LiveComponentTestHelper::callLiveAction(
            $client,
            $secondList,
            'search',
            [],
            [
                'user_list_filter' => [
                    'email' => 'admin@live-grid.com',
                ],
            ],
        );
        $updatedComponents = LiveComponentTestHelper::findLiveComponents($crawler);
        $updatedSecond = $this->findComponentByName($updatedComponents, $secondList->componentName());

        $queryString = $updatedFirst->queryString();
        if ('' !== $updatedSecond->queryString()) {
            $queryString .= '&' . $updatedSecond->queryString();
        }

        $client->request('GET', '/admin/users/?' . $queryString);
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        $reloaded = LiveComponentTestHelper::findLiveComponents($client->getCrawler());
        $reloadedFirst = $this->findComponentByName($reloaded, $firstList->componentName());
        $reloadedSecond = $this->findComponentByName($reloaded, $secondList->componentName());

        self::assertSame(2, $reloadedFirst->intProp('page'));
        self::assertCount(10, $reloadedFirst->node->filter('.flex-table-item'));
        self::assertSame(1, $reloadedSecond->intProp('page'));
        self::assertCount(1, $reloadedSecond->node->filter('.flex-table-item'));
        self::assertStringContainsString('admin@live-grid.com', $reloadedSecond->node->text());
    }

    /**
     * @param LiveComponentSnapshot[] $components
     */
    private function findComponentByName(array $components, string $name): LiveComponentSnapshot
    {
        foreach ($components as $component) {
            if ($component->componentName() === $name) {
                return $component;
            }
        }

        self::fail(sprintf('Component "%s" not found.', $name));
    }
}
