<?php

namespace App\LiveGrid\Pagination\Exception;

final class PageNumberOutOfRangeException extends \OutOfRangeException
{
    public function __construct(
        string $message,
        private readonly int $maxPageNumber,
    ) {
        parent::__construct($message);
    }

    public function getMaxPageNumber(): int
    {
        return $this->maxPageNumber;
    }
}