<?php

declare(strict_types=1);

namespace App\Tests\E2e\Client;

use Playwright\Frame\FrameInterface;
use Playwright\Network\RequestInterface;
use Playwright\Network\ResponseInterface;

/**
 * @internal
 */
final class BufferedPostDataRequest implements RequestInterface
{
    public function __construct(
        private readonly RequestInterface $inner,
        private readonly string $postData,
    ) {
    }

    public function postData(): string
    {
        return $this->postData;
    }

    public function url(): string
    {
        return $this->inner->url();
    }

    public function method(): string
    {
        return $this->inner->method();
    }

    public function headers(): array
    {
        return $this->inner->headers();
    }

    public function headerValue(string $name): ?string
    {
        return $this->inner->headerValue($name);
    }

    public function headersArray(): array
    {
        return $this->inner->headersArray();
    }

    public function allHeaders(): array
    {
        return $this->inner->allHeaders();
    }

    public function postDataJSON(): ?array
    {
        return $this->inner->postDataJSON();
    }

    public function resourceType(): string
    {
        return $this->inner->resourceType();
    }

    public function isNavigationRequest(): bool
    {
        return $this->inner->isNavigationRequest();
    }

    public function postDataBuffer(): ?string
    {
        return $this->inner->postDataBuffer();
    }

    public function failure(): ?array
    {
        return $this->inner->failure();
    }

    public function frame(): ?FrameInterface
    {
        return $this->inner->frame();
    }

    public function redirectedFrom(): ?RequestInterface
    {
        return $this->inner->redirectedFrom();
    }

    public function redirectedTo(): ?RequestInterface
    {
        return $this->inner->redirectedTo();
    }

    public function response(): ?ResponseInterface
    {
        return $this->inner->response();
    }

    public function serviceWorker(): mixed
    {
        return $this->inner->serviceWorker();
    }

    public function sizes(): array
    {
        return $this->inner->sizes();
    }

    public function timing(): array
    {
        return $this->inner->timing();
    }
}
