<?php

namespace App\LiveGrid\Filter;

use App\LiveGrid\Port\DataSourceInterface;
use App\LiveGrid\Port\FilterHandlerInterface;
use App\LiveGrid\Port\QueryBuilderAwareDataSourceInterface;
use Spiriit\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Component\Form\FormInterface;

final class SpiriitFilterHandler implements FilterHandlerInterface
{
    public function __construct(
        private readonly FilterBuilderUpdaterInterface $filterBuilderUpdater,
    ) {
    }

    public function supports(DataSourceInterface $dataSource): bool
    {
        return $dataSource instanceof QueryBuilderAwareDataSourceInterface;
    }

    public function apply(FormInterface $form, DataSourceInterface $dataSource): void
    {
        if (!$dataSource instanceof QueryBuilderAwareDataSourceInterface) {
            return;
        }

        $this->filterBuilderUpdater->addFilterConditions($form, $dataSource->getQueryBuilder());
    }
}
