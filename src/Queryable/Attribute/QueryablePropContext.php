<?php

namespace App\Queryable\Attribute;

final class QueryablePropContext
{
    public function __construct(
        private readonly QueryableProp $queryableProp,
        private readonly \ReflectionProperty $reflectionProperty,
    ) {
    }

    public function queryableProp(): QueryableProp
    {
        return $this->queryableProp;
    }

    public function reflectionProperty(): \ReflectionProperty
    {
        return $this->reflectionProperty;
    }
}
