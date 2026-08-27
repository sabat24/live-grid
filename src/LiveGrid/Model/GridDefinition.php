<?php

namespace App\LiveGrid\Model;

final readonly class GridDefinition
{
    /**
     * @param list<FieldDefinition> $fields
     * @param list<ActionDefinition> $actions
     * @param list<int> $allowedPaginate
     * @param array<string, mixed> $extras Adapter-specific payload (e.g. Sylius GridView)
     */
    public function __construct(
        public string $name,
        public array $fields = [],
        public array $actions = [],
        public array $allowedPaginate = [10, 25, 50],
        public bool $filterable = false,
        public array $extras = [],
    ) {
    }

    /**
     * @return list<FieldDefinition>
     */
    public function enabledFields(): array
    {
        $fields = array_values(array_filter(
            $this->fields,
            static fn (FieldDefinition $field): bool => $field->enabled,
        ));
        usort($fields, static fn (FieldDefinition $a, FieldDefinition $b): int => $a->position <=> $b->position);

        return $fields;
    }

    /**
     * @return list<ActionDefinition>
     */
    public function enabledActions(?string $group = null): array
    {
        $actions = array_values(array_filter(
            $this->actions,
            static function (ActionDefinition $action) use ($group): bool {
                if (!$action->enabled) {
                    return false;
                }

                return $group === null || $action->group === $group;
            },
        ));
        usort($actions, static fn (ActionDefinition $a, ActionDefinition $b): int => $a->position <=> $b->position);

        return $actions;
    }

    public function extra(string $key, mixed $default = null): mixed
    {
        return $this->extras[$key] ?? $default;
    }
}
