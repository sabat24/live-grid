<?php

declare(strict_types=1);

namespace App\Tests\E2e\Admin;

use App\Tests\E2e\AbstractUserListE2eTestCase;
use App\Tests\E2e\Support\UserListBrowserTrait;
use Playwright\Page\PageInterface;

/**
 * @property-read PageInterface $page
 */
final class UserListUrlSyncE2eTest extends AbstractUserListE2eTestCase
{
    use UserListBrowserTrait;

    public function testPaginationUpdatesUrlAndSurvivesReload(): void
    {
        $this->visitUsersIndex();

        $this->clickPaginationPage(0, 2);
        $this->assertListCurrentPage(0, 2);

        $searchAfterClick = $this->pollLocationSearchContaining('page');
        self::assertStringContainsString('page', $searchAfterClick);

        $this->reloadAndWait();

        $searchAfterReload = $this->page->evaluate('() => window.location.search');
        self::assertIsString($searchAfterReload);
        self::assertStringContainsString('page', $searchAfterReload);
        self::assertSame($searchAfterClick, $searchAfterReload);
        $this->assertListCurrentPage(0, 2);
    }

    public function testResultsPerPageDropdownUpdatesUrlAndSurvivesReload(): void
    {
        $this->visitUsersIndex();

        $this->openResultsPerPageDropdown(0);
        $this->clickResultsPerPageOption(0, 50);

        $searchAfterClick = $this->pollLocationSearchContaining('resultsPerPage');
        self::assertStringContainsString('resultsPerPage', $searchAfterClick);
        $this->assertListRowCount(0, 26);

        $this->reloadAndWait();

        $searchAfterReload = $this->page->evaluate('() => window.location.search');
        self::assertIsString($searchAfterReload);
        self::assertStringContainsString('resultsPerPage', $searchAfterReload);
        self::assertSame($searchAfterClick, $searchAfterReload);
        $this->assertListRowCount(0, 26);
    }

    public function testDualListPaginationMergesUrlAndSurvivesReload(): void
    {
        $this->visitUsersIndex();

        $componentNames = $this->getListComponentNames();
        self::assertCount(2, $componentNames);

        $this->clickPaginationPage(0, 2);
        $this->assertListCurrentPage(0, 2);

        $this->clickPaginationPage(1, 3);
        $this->assertListCurrentPage(1, 3);

        $searchAfterClick = $this->pollLocationSearchContaining('page');
        self::assertStringContainsString($componentNames[0], $searchAfterClick);
        self::assertStringContainsString($componentNames[1], $searchAfterClick);

        $this->reloadAndWait();

        $searchAfterReload = $this->page->evaluate('() => window.location.search');
        self::assertIsString($searchAfterReload);
        self::assertSame($searchAfterClick, $searchAfterReload);
        $this->assertListCurrentPage(0, 2);
        $this->assertListCurrentPage(1, 3);
    }

    public function testBrowserBackForwardRestoresListState(): void
    {
        $this->visitUsersIndex();

        $this->assertListCurrentPage(0, 1);

        $this->clickPaginationPage(0, 2);
        $this->assertListCurrentPage(0, 2);
        $this->pollLocationSearchContaining('page');

        $this->page->goBack();
        $this->assertListCurrentPage(0, 1);

        $this->page->goForward();
        $this->assertListCurrentPage(0, 2);
    }

    /**
     * @return list<string>
     */
    private function getListComponentNames(): array
    {
        $names = $this->page->evaluate(
            <<<'JS'
            () => [...document.querySelectorAll('[data-live-url-value]')].filter((el) => el.querySelector('.flex-table-wrapper') !== null).map((el) => {
                const props = JSON.parse(el.getAttribute('data-live-props-value') || '{}');
                return props.componentName || '';
            })
            JS,
        );

        self::assertIsArray($names);

        return array_values(array_filter($names, static fn(mixed $name): bool => is_string($name) && '' !== $name));
    }
}
