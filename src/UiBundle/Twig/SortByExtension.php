<?php

namespace App\UiBundle\Twig;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class SortByExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('sort_by', [$this, 'sortBy']),
        ];
    }

    /**
     * @param iterable<object|array<string, mixed>> $iterable
     *
     * @return list<object|array<string, mixed>>
     *
     * @throws NoSuchPropertyException
     */
    public function sortBy(iterable $iterable, string $field, string $order = 'ASC'): array
    {
        $array = $this->transformIterableToArray($iterable);

        usort(
            $array,
            function (object | array $firstElement, object | array $secondElement) use ($field, $order): int {
                $accessor = PropertyAccess::createPropertyAccessor();

                $firstProperty = $this->stringifySortValue($accessor->getValue($firstElement, $field));
                $secondProperty = $this->stringifySortValue($accessor->getValue($secondElement, $field));

                $result = strnatcasecmp($firstProperty, $secondProperty);
                if ($order === 'DESC') {
                    $result *= -1;
                }

                return $result;
            },
        );

        return $array;
    }

    /**
     * @param iterable<object|array<string, mixed>> $iterable
     *
     * @return list<object|array<string, mixed>>
     */
    private function transformIterableToArray(iterable $iterable): array
    {
        if (is_array($iterable)) {
            return array_values($iterable);
        }

        return array_values(iterator_to_array($iterable));
    }

    private function stringifySortValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value) || $value instanceof \Stringable) {
            return (string) $value;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Cannot sort by field value of type "%s".',
                get_debug_type($value),
            ),
        );
    }
}
