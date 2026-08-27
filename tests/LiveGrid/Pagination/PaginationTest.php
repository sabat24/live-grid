<?php

namespace App\Tests\LiveGrid\Pagination;

use App\LiveGrid\Pagination\Exception\PageNumberOutOfRangeException;
use App\LiveGrid\Pagination\Model\Pagination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaginationTest extends TestCase
{
    public function testDefaultIgnoreKeepsOutOfRangePageNumber(): void
    {
        $pagination = new Pagination();
        $view = $pagination->paginate(totalCount: 5, page: 2, limit: 10);

        self::assertSame(2, $view->getCurrentPageNumber());
        self::assertSame(5, $view->getTotalItemCount());
    }

    public function testThrowExceptionWhenPageBeyondLast(): void
    {
        $pagination = new Pagination();

        try {
            $pagination->paginate(
                totalCount: 5,
                page: 2,
                limit: 10,
                options: [Pagination::PAGE_OUT_OF_RANGE => Pagination::PAGE_OUT_OF_RANGE_THROW_EXCEPTION],
            );
            self::fail('Expected PageNumberOutOfRangeException.');
        } catch (PageNumberOutOfRangeException $e) {
            self::assertSame(1, $e->getMaxPageNumber());
            self::assertStringContainsString('Page number: 2 is out of range.', $e->getMessage());
        }
    }

    public function testThrowExceptionDoesNotFireForPageOneWithEmptyTotal(): void
    {
        $pagination = new Pagination();
        $view = $pagination->paginate(
            totalCount: 0,
            page: 1,
            limit: 10,
            options: [Pagination::PAGE_OUT_OF_RANGE => Pagination::PAGE_OUT_OF_RANGE_THROW_EXCEPTION],
        );

        self::assertSame(1, $view->getCurrentPageNumber());
        self::assertSame(0, $view->getTotalItemCount());
    }

    #[DataProvider('fixOutOfRangeProvider')]
    public function testFixClampsToLastPage(int $totalCount, int $requestedPage, int $limit, int $expectedPage): void
    {
        $pagination = new Pagination();
        $view = $pagination->paginate(
            totalCount: $totalCount,
            page: $requestedPage,
            limit: $limit,
            options: [Pagination::PAGE_OUT_OF_RANGE => Pagination::PAGE_OUT_OF_RANGE_FIX],
        );

        self::assertSame($expectedPage, $view->getCurrentPageNumber());
    }

    /**
     * @return iterable<string, array{int, int, int, int}>
     */
    public static function fixOutOfRangeProvider(): iterable
    {
        yield '25 items, page 5 at 10 per page clamps to 3' => [25, 5, 10, 3];
        yield '5 items, page 2 at 10 per page clamps to 1' => [5, 2, 10, 1];
    }
}