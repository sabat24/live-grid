<?php

declare(strict_types=1);

namespace App\Component\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class Builder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly MatcherInterface $matcher,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return ItemInterface[]
     */
    private function createMenuItems(): array
    {
        $items = [];

        $items[] = $this->factory->createItem('Dashboard', ['route' => 'app_admin_dashboard'])
            ->setExtra('icon', 'grid');

        $items[] = $this->factory->createItem(
            $this->translator->trans('app.ui.users'),
            ['route' => 'app_admin_user_index'],
        )
            ->setExtra('icon', 'briefcase')
            ->setExtra('parentRoutePattern', 'app_admin_user');

        return $items;
    }

    public function AdminSidebar(): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $items = $this->createMenuItems();

        foreach ($items as $item) {
            $menu->addChild($item);
        }

        return $menu;
    }

    public function AdminMainMobileSidebar(): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'icon-side-menu');

        $items = $this->createMenuItems();

        foreach ($items as $item) {
            $menu->addChild($item);
        }

        return $menu;
    }

    public function AdminMobileSubSidebar(): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'submenu');
        $items = $this->createMenuItems();

        foreach ($items as $item) {
            if (!$this->matcher->isAncestor($item)) {
                continue;
            }
            $menu->addChild($item);
        }

        return $menu;
    }
}
