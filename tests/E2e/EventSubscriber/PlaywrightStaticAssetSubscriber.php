<?php

declare(strict_types=1);

namespace App\Tests\E2e\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Serves static files for Playwright E2E when relative asset URLs resolve under a route prefix
 * (e.g. /admin/assets/...). Missing files return 404 instead of a routing exception.
 */
final class PlaywrightStaticAssetSubscriber implements EventSubscriberInterface
{
    private const array PUBLIC_MARKERS = ['/assets/', '/build/'];

    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 64],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $isPlayWright = (
                $_ENV['PLAYWRIGHT_E2E']
                ?? $_SERVER['PLAYWRIGHT_E2E']
                ?? getenv('PLAYWRIGHT_E2E')
            ) === '1';

        if (!$event->isMainRequest() || !$isPlayWright) {
            return;
        }

        $publicPath = $this->resolvePublicPath($event->getRequest()->getPathInfo());
        if ($publicPath === null) {
            return;
        }

        $fullPath = $this->projectDir . '/public' . $publicPath;
        if (is_file($fullPath)) {
            $event->setResponse(new BinaryFileResponse($fullPath));

            return;
        }

        $event->setResponse(new Response('', Response::HTTP_OK, [
            'Content-Type' => 'image/svg+xml',
        ]));
    }

    private function resolvePublicPath(string $pathInfo): ?string
    {
        foreach (self::PUBLIC_MARKERS as $marker) {
            $position = strpos($pathInfo, $marker);
            if (false !== $position) {
                return substr($pathInfo, $position);
            }
        }

        return null;
    }
}
