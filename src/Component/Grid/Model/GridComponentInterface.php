<?php

namespace App\Component\Grid\Model;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

interface GridComponentInterface
{
    public function getSearchFormInstance(): FormInterface;
    public function createFilterQueryBuilder(): QueryBuilder;

    public function getConfiguration(): array;
}
