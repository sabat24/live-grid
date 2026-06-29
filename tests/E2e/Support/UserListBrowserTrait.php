<?php

declare(strict_types=1);

namespace App\Tests\E2e\Support;

use Playwright\Page\PageInterface;
use Playwright\Symfony\Test\PlaywrightTestCase;

/**
 * @mixin PlaywrightTestCase
 * @property-read PageInterface $page
 */
trait UserListBrowserTrait
{
    private const string USER_LIST_ROOT = '[data-live-url-value]:has(.flex-table-wrapper)';

    protected function visitUsersIndex(): void
    {
        $this->visit('/admin/users');
        $this->assertResponseIsSuccessful();
        $this->waitForStimulus();
        $this->removeLoaders();
    }

    protected function waitForStimulus(): void
    {
        for ($i = 0; $i < 50; ++$i) {
            $loaded = $this->page->evaluate('() => typeof window.Stimulus !== "undefined"');
            if (true === $loaded) {
                return;
            }
            usleep(200_000);
        }

        self::fail('Stimulus did not load — run `yarn encore production` before E2E.');
    }

    protected function removeLoaders(): void
    {
        $this->page->evaluate(
            '() => document.querySelectorAll(".infraloader, .pageloader").forEach(el => el.remove())',
        );
    }

    protected function pollLocationSearchContaining(string $needle): string
    {
        for ($i = 0; $i < 50; ++$i) {
            $search = $this->page->evaluate('() => window.location.search');
            if (is_string($search) && str_contains($search, $needle)) {
                return $search;
            }
            usleep(200_000);
        }

        $search = $this->page->evaluate('() => window.location.search');

        return is_string($search) ? $search : '';
    }

    protected function assertListCurrentPage(int $listIndex, int $expectedPage): void
    {
        for ($i = 0; $i < 50; ++$i) {
            $currentPage = $this->readListCurrentPage($listIndex);
            if ((string) $expectedPage === $currentPage) {
                self::assertSame((string) $expectedPage, $currentPage);

                return;
            }
            usleep(200_000);
        }

        self::assertSame(
            (string) $expectedPage,
            $this->readListCurrentPage($listIndex),
            sprintf('List %d pagination did not reach page %d.', $listIndex, $expectedPage),
        );
    }

    private function readListCurrentPage(int $listIndex): string
    {
        /** @var mixed $page */
        $page = $this->page->evaluate(
            <<<'JS'
            (listIndex) => {
                const roots = [...document.querySelectorAll('[data-live-url-value]')].filter(
                    (el) => el.querySelector('.flex-table-wrapper') !== null
                );
                const current = roots[listIndex]?.querySelector('.pagination-link.is-current');
                return current ? current.textContent.trim() : '';
            }
            JS,
            $listIndex,
        );

        return is_string($page) ? $page : '';
    }

    protected function assertListRowCount(int $listIndex, int $expectedCount): void
    {
        $listRoot = $this->page->locator(self::USER_LIST_ROOT)->nth($listIndex);
        $rows = $listRoot->locator('.flex-table-item');

        for ($i = 0; $i < 50; ++$i) {
            if ($expectedCount === $rows->count()) {
                self::assertSame($expectedCount, $rows->count());

                return;
            }
            usleep(200_000);
        }

        self::assertSame(
            $expectedCount,
            $rows->count(),
            sprintf('List %d expected %d rows.', $listIndex, $expectedCount),
        );
    }

    protected function openResultsPerPageDropdown(int $listIndex): void
    {
        $this->page->locator(self::USER_LIST_ROOT)->nth($listIndex)->locator('.select-box')->click(['force' => true]);
    }

    protected function clickResultsPerPageOption(int $listIndex, int $resultsPerPage): void
    {
        $selector = sprintf(
            '[data-live-action-param="updateResultsPerPage"][data-live-results-per-page-param="%d"]',
            $resultsPerPage,
        );
        $this->page->locator(self::USER_LIST_ROOT)->nth($listIndex)->locator($selector)->click(['force' => true]);
    }

    protected function clickPaginationPage(int $listIndex, int $page): void
    {
        /** @var mixed $clicked */
        $clicked = $this->page->evaluate(
            <<<'JS'
            ([listIndex, page]) => {
                const roots = [...document.querySelectorAll('[data-live-url-value]')].filter(
                    (el) => el.querySelector('.flex-table-wrapper') !== null
                );
                const root = roots[listIndex];
                if (!root) {
                    return false;
                }

                const link = root.querySelector(
                    `[data-live-action-param="updatePage"][data-live-page-param="${String(page)}"]`
                );
                if (!link) {
                    return false;
                }

                link.click();

                return true;
            }
            JS,
            [$listIndex, $page],
        );

        self::assertTrue(
            true === $clicked,
            sprintf('Could not click pagination page %d on list %d.', $page, $listIndex),
        );
    }

    protected function reloadAndWait(): void
    {
        $this->page->reload();
        $this->page->waitForLoadState('networkidle');
        $this->waitForStimulus();
        $this->removeLoaders();
    }

    protected function pollLocationSearchNotContaining(string $needle): string
    {
        for ($i = 0; $i < 50; ++$i) {
            $search = $this->page->evaluate('() => window.location.search');
            if (is_string($search) && !str_contains($search, $needle)) {
                return $search;
            }
            usleep(200_000);
        }

        $search = $this->page->evaluate('() => window.location.search');

        self::fail(
            sprintf(
                'URL search still contains "%s" after polling: %s',
                $needle,
                is_string($search) ? $search : '',
            ),
        );
    }

    protected function getListComponentName(int $listIndex): string
    {
        $names = $this->getListComponentNames();
        self::assertArrayHasKey($listIndex, $names);

        return $names[$listIndex];
    }

    /**
     * @return list<string>
     */
    protected function getListComponentNames(): array
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

    protected function visitUsersIndexWithComponentQuery(string $query): void
    {
        $this->visit('/admin/users?' . $query);
        $this->assertResponseIsSuccessful();
        $this->waitForStimulus();
        $this->removeLoaders();
    }

    protected function clearEmailFilterAndSubmit(int $listIndex): void
    {
        $input = $this->page->locator(self::USER_LIST_ROOT)->nth($listIndex)->locator(
            'input[type="text"][name*="email"]',
        );
        $input->fill('');

        $submitted = $this->page->evaluate(
            <<<'JS'
            (listIndex) => {
                const roots = [...document.querySelectorAll('[data-live-url-value]')].filter(
                    (el) => el.querySelector('.flex-table-wrapper') !== null
                );
                const form = roots[listIndex]?.querySelector('form');
                if (!form) {
                    return false;
                }

                form.requestSubmit();

                return true;
            }
            JS,
            $listIndex,
        );

        self::assertTrue(
            true === $submitted,
            sprintf('Could not submit cleared email filter on list %d.', $listIndex),
        );
    }
}
