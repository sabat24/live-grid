<?php

namespace App\Component\LiveComponent\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class QueryableProp
{
    public function __construct(
        private readonly bool $writable = true,
        private readonly ?string $fieldName = null,
    ) {
    }

    public function isReadonly(): bool
    {
        return !$this->writable;
    }

    public function calculateFieldName(object $component, string $fallback): string
    {
        if (!$this->fieldName) {
            return $fallback;
        }

        if (str_ends_with($this->fieldName, '()')) {
            return $component->{trim($this->fieldName, '()')}();
        }

        return $this->fieldName;
    }
}
