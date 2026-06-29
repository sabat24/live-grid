<?php

declare(strict_types=1);

namespace App\Tests\E2e\Client;

use Playwright\Network\RequestInterface;
use Playwright\Symfony\Client\RequestConverter;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Playwright returns null from postData() for multipart/form-data (e.g. UX Live Component actions).
 * Fall back to postDataBuffer() so the kernel receives the FormData body.
 */
final class MultipartAwareRequestConverter extends RequestConverter
{
    public function convertToSymfonyRequest(RequestInterface $playwrightRequest): SymfonyRequest
    {
        if (null === $playwrightRequest->postData() && null !== $playwrightRequest->postDataBuffer()) {
            $playwrightRequest = new BufferedPostDataRequest(
                $playwrightRequest,
                $playwrightRequest->postDataBuffer(),
            );
        }

        return parent::convertToSymfonyRequest($playwrightRequest);
    }
}
