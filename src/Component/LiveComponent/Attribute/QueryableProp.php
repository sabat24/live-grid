<?php

namespace App\Component\LiveComponent\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class QueryableProp
{
    public function __construct(
        private bool $writable = true,
        private ?string $fieldName = null,
    ) {
    }

    public function isReadonly(): bool
    {
        return !$this->writable;
    }

    public function calculateFieldName(object $component, string $fallback): string
    {
        if ($this->fieldName === null) {
            return $fallback;
        }

        if (str_ends_with($this->fieldName, '()')) {
            $methodName = substr($this->fieldName, 0, -2);
            if (!method_exists($component, $methodName)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'QueryableProp field name "%s" refers to missing method "%s::%s()".',
                        $this->fieldName,
                        $component::class,
                        $methodName,
                    ),
                );
            }

            $callable = [$component, $methodName];
            if (!is_callable($callable)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'QueryableProp field name "%s" refers to non-callable method "%s::%s()".',
                        $this->fieldName,
                        $component::class,
                        $methodName,
                    ),
                );
            }

            $result = $callable();
            if (!is_string($result)) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'Method "%s::%s()" must return a string for QueryableProp, %s returned.',
                        $component::class,
                        $methodName,
                        get_debug_type($result),
                    ),
                );
            }

            return $result;
        }

        return $this->fieldName;
    }
}
