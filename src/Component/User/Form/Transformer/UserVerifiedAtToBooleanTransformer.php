<?php

namespace App\Component\User\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<?\DateTimeInterface, bool>
 */
final class UserVerifiedAtToBooleanTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): bool
    {
        return $value instanceof \DateTimeInterface;
    }

    public function reverseTransform(mixed $value): ?\DateTime
    {
        return $value === true ? new \DateTime() : null;
    }
}
