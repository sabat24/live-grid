<?php

namespace App\Tests\Component;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

final class LiveComponentTestHelper
{
    /**
     * @return list<LiveComponentSnapshot>
     */
    public static function findLiveComponents(Crawler $crawler): array
    {
        $components = [];

        $crawler->filter('[data-live-url-value]')->each(function (Crawler $node) use (&$components) {
            $components[] = self::snapshotFromNode($node);
        });

        return $components;
    }

    public static function snapshotFromNode(Crawler $node): LiveComponentSnapshot
    {
        $props = self::decodeJson($node->attr('data-live-props-value') ?? '{}');

        return new LiveComponentSnapshot(
            $node,
            $node->attr('data-live-url-value') ?? '',
            $props,
            $props,
            $node->attr('data-live-csrf-value'),
        );
    }

    /**
     * @param array<string, mixed> $args
     * @param array<string, mixed> $updated
     * @throws \JsonException
     */
    public static function callLiveAction(
        KernelBrowser $client,
        LiveComponentSnapshot $component,
        string $action,
        array $args = [],
        array $updated = [],
    ): Crawler {
        $client->request(
            'POST',
            $component->url.'/'.$action,
            parameters: [
                'data' => json_encode([
                    'props' => $component->props,
                    'updated' => self::flattenUpdated($updated),
                    'args' => $args,
                    'children' => [],
                    'propsFromParent' => [],
                ], \JSON_THROW_ON_ERROR),
            ],
            server: [
                'HTTP_ACCEPT' => 'application/vnd.live-component+html',
                'HTTP_X-CSRF-TOKEN' => $component->csrfToken ?? '',
            ],
        );

        if (!$client->getResponse()->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'Live action failed with status %d: %s',
                $client->getResponse()->getStatusCode(),
                $client->getResponse()->getContent(),
            ));
        }

        return $client->getCrawler();
    }

    /**
     * @return array<string, mixed>
     * @throws \JsonException
     */
    private static function decodeJson(string $value): array
    {
        if ('' === $value) {
            return [];
        }

        $decoded = json_decode(html_entity_decode($value, \ENT_QUOTES), true, 512, \JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @param array<string, mixed> $updated
     *
     * @return array<string, mixed>
     */
    private static function flattenUpdated(array $updated, string $prefix = ''): array
    {
        $result = [];

        foreach ($updated as $key => $value) {
            $path = '' === $prefix ? $key : $prefix.'.'.$key;
            if (is_array($value)) {
                /** @var array<string, mixed> $nested */
                $nested = $value;
                $result += self::flattenUpdated($nested, $path);
            } else {
                $result[$path] = $value;
            }
        }

        return $result;
    }
}

final class LiveComponentSnapshot
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $props
     */
    public function __construct(
        public readonly Crawler $node,
        public readonly string $url,
        public readonly array $data,
        public readonly array $props,
        public readonly ?string $csrfToken,
    ) {
    }

    public function componentName(): string
    {
        $name = $this->data['componentName'] ?? $this->props['componentName'] ?? '';

        return is_string($name) ? $name : '';
    }

    public function queryString(): string
    {
        $queryString = $this->data['queryString'] ?? $this->props['queryString'] ?? '';

        return is_string($queryString) ? $queryString : '';
    }

    public function intProp(string $key): int
    {
        $value = $this->data[$key] ?? $this->props[$key] ?? 0;

        return is_numeric($value) ? (int) $value : 0;
    }
}
