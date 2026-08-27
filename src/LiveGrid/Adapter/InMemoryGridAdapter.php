<?php

namespace App\LiveGrid\Adapter;

use App\LiveGrid\DataSource\InMemoryDataSource;
use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\FieldDefinition;
use App\LiveGrid\Model\GridDefinition;
use App\LiveGrid\Model\LiveGridConfiguration;
use App\LiveGrid\Model\Page;
use App\LiveGrid\Port\GridAdapterInterface;

/**
 * Simple adapter for tests and non-Sylius demos.
 * Options: items (list), fields (list of field defs as arrays), allowed_paginate, filterable.
 */
final class InMemoryGridAdapter implements GridAdapterInterface
{
    public function supports(LiveGridConfiguration $configuration): bool
    {
        return $configuration->adapter === 'in_memory';
    }

    public function getDefinition(LiveGridConfiguration $configuration): GridDefinition
    {
        $fields = [];
        $rawFields = $configuration->options['fields'] ?? [];
        if (is_array($rawFields)) {
            $position = 0;
            foreach ($rawFields as $name => $field) {
                if (!is_array($field)) {
                    continue;
                }
                $fieldName = is_string($name) ? $name : (is_string($field['name'] ?? null) ? $field['name'] : (string) $position);
                $type = is_string($field['type'] ?? null) ? $field['type'] : 'string';
                $label = is_string($field['label'] ?? null) ? $field['label'] : $fieldName;
                $path = is_string($field['path'] ?? null) ? $field['path'] : $fieldName;
                $options = $field['options'] ?? [];
                if (!is_array($options)) {
                    $options = [];
                }
                /** @var array<string, mixed> $typedOptions */
                $typedOptions = $options;

                $fields[] = new FieldDefinition(
                    name: $fieldName,
                    type: $type,
                    label: $label,
                    path: $path,
                    enabled: (bool) ($field['enabled'] ?? true),
                    sortable: (bool) ($field['sortable'] ?? false),
                    position: is_numeric($field['position'] ?? null) ? (int) $field['position'] : $position,
                    options: $typedOptions,
                );
                ++$position;
            }
        }

        $allowed = $configuration->options['allowed_paginate'] ?? [10, 25, 50];
        if (!is_array($allowed)) {
            $allowed = [10, 25, 50];
        }

        $allowedPaginate = [];
        foreach ($allowed as $value) {
            if (is_numeric($value)) {
                $allowedPaginate[] = (int) $value;
            }
        }

        return new GridDefinition(
            name: $configuration->name,
            fields: $fields,
            actions: [],
            allowedPaginate: $allowedPaginate !== [] ? $allowedPaginate : [10, 25, 50],
            filterable: (bool) ($configuration->options['filterable'] ?? false),
        );
    }

    public function getPage(LiveGridConfiguration $configuration, Criteria $criteria, ?object $filterForm = null): Page
    {
        $items = $configuration->options['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        /** @var list<mixed> $list */
        $list = array_values($items);
        $dataSource = new InMemoryDataSource($list);

        return $dataSource->paginate($criteria);
    }
}
