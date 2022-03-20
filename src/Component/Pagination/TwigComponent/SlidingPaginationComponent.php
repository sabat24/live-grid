<?php

namespace App\Component\Pagination\TwigComponent;

use App\Component\Pagination\Model\Pagination;
use App\Component\Pagination\Model\ViewData;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent('admin:sliding_pagination')]
final class SlidingPaginationComponent
{
    public int $page = 1;
    public int $totalItemCount = 0;
    public int $resultsPerPage = 10;

    public ViewData $viewData;

    #[PreMount(1)]
    public function validateIncomingData(array $data): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['page' => 1, 'totalItemCount' => 0]);
        $resolver->setRequired(['page', 'totalItemCount']);
        $resolver->setDefined(['resultsPerPage']);

        return $resolver->resolve($data);
    }

    #[PostMount]
    public function createViewData(array $data): array
    {
        $this->updateViewData();

        return $data;
    }

    private function updateViewData(): void
    {
        $pagination = new Pagination();
        $paginationView = $pagination->paginate($this->totalItemCount, $this->page, $this->resultsPerPage);
        $this->viewData = $paginationView->getPaginationData();
    }
}
