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
     * @throws NoSuchPropertyException
     */
    public function sortBy(iterable $iterable, string $field, string $order = 'ASC'): array
    {
        $array = $this->transformIterableToArray($iterable);

        usort(
            $array,
            function (mixed $firstElement, mixed $secondElement) use ($field, $order) {
                $accessor = PropertyAccess::createPropertyAccessor();

                $firstProperty = (string) $accessor->getValue($firstElement, $field);
                $secondProperty = (string) $accessor->getValue($secondElement, $field);

                $result = strnatcasecmp($firstProperty, $secondProperty);
                if ($order === 'DESC') {
                    $result *= -1;
                }

                return $result;
            }
        );

        return $array;
    }

    /**
     * @param \Traversable $iterable
     */
    private function transformIterableToArray(iterable $iterable): array
    {
        if (is_array($iterable)) {
            return $iterable;
        }

        return iterator_to_array($iterable);
    }
}
