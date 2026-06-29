<?php

namespace App\Component\Grid\Model;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

interface GridComponentInterface
{
    /**
     * @return FormInterface<mixed>
     */
    public function getSearchFormInstance(): FormInterface;

    public function createFilterQueryBuilder(): QueryBuilder;

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array;
}
