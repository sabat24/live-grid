<?php

namespace App\Component\User\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

final class UserVerifiedAtToBooleanTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): bool
    {
        return (bool) $value;
    }

    public function reverseTransform(mixed $value): ?\DateTime
    {
        return $value ? new \DateTime() : null;
    }
}
