<?php

namespace App\Component\Pagination\TwigComponent;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent('admin:results_per_page_select')]
final class ResultsPerPageSelectComponent
{
    public array $resultsPerPageChoices = [10, 25, 50, 100];
    public int $resultsPerPage = 10;

    /**
     * @throws \Exception
     */
    #[PreMount(1)]
    public function validateIncomingData(array $data): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['resultsPerPage']);
        $resolver->setDefined(['resultsPerPageChoices']);

        $data = $resolver->resolve($data);

        $this->validateResultsPerPage($data['resultsPerPage']);

        return $data;
    }

    /**
     * @throws \Exception
     */
    private function validateResultsPerPage(int $resultsPerPage): void
    {
        if (!in_array($resultsPerPage, $this->resultsPerPageChoices)) {
            throw new \Exception('Invalid resultsPerPage');
        }
    }
}
