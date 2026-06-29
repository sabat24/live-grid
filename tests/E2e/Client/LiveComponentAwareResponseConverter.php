<?php

declare(strict_types=1);

namespace App\Tests\E2e\Client;

use Playwright\Symfony\Client\ResponseConverter;

/**
 * Playwright Symfony treats unknown text-like MIME types as binary (base64).
 * Live Component responses must reach the browser as plain HTML for morphdom.
 */
final class LiveComponentAwareResponseConverter extends ResponseConverter
{
    public function isBinaryContentType(?string $contentType): bool
    {
        if (null !== $contentType && str_starts_with(strtolower($contentType), 'application/vnd.live-component+html')) {
            return false;
        }

        return parent::isBinaryContentType($contentType);
    }
}
