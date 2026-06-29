<?php

namespace App\Component\Pagination\TwigComponent;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent('admin:results_per_page_select')]
final class ResultsPerPageSelectComponent
{
    /** @var list<int> */
    public array $resultsPerPageChoices = [10, 25, 50, 100];

    public int $resultsPerPage = 10;

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    #[PreMount(1)]
    public function validateIncomingData(array $data): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['resultsPerPage']);
        $resolver->setDefined(['resultsPerPageChoices']);

        $data = $resolver->resolve($data);

        $resultsPerPage = $data['resultsPerPage'];
        if (!is_int($resultsPerPage)) {
            throw new \InvalidArgumentException('resultsPerPage must be an integer.');
        }

        $this->validateResultsPerPage($resultsPerPage);

        /** @var array<string, mixed> $resolved */
        $resolved = $data;

        return $resolved;
    }

    /**
     * @throws \Exception
     */
    private function validateResultsPerPage(int $resultsPerPage): void
    {
        if (!\in_array($resultsPerPage, $this->resultsPerPageChoices, true)) {
            throw new \Exception('Invalid resultsPerPage');
        }
    }
}
