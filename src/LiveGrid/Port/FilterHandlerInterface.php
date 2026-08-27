<?php

namespace App\LiveGrid\Port;

use Symfony\Component\Form\FormInterface;

interface FilterHandlerInterface
{
    /**
     * @param FormInterface<mixed> $form
     */
    public function apply(FormInterface $form, DataSourceInterface $dataSource): void;

    public function supports(DataSourceInterface $dataSource): bool;
}
