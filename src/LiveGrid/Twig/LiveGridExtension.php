<?php

namespace App\LiveGrid\Twig;

use App\LiveGrid\Port\HookRegistryInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LiveGridExtension extends AbstractExtension
{
    public function __construct(
        private readonly HookRegistryInterface $hookRegistry,
        private readonly Environment $twig,
    ) {
    }

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('live_grid_hook', $this->renderHook(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function renderHook(string $hookName, array $context = []): string
    {
        $output = '';
        foreach ($this->hookRegistry->getHooks($hookName) as $hook) {
            $output .= $this->twig->render($hook->template, $context);
        }

        return $output;
    }
}
