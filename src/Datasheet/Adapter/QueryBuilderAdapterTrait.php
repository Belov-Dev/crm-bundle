<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\DatasheetExtended;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

trait QueryBuilderAdapterTrait
{
    protected function cloneQueryBuilder(DatasheetExtended $datasheet, $applyFilters = false): QueryBuilder
    {
        $queryBuilder = clone $datasheet->getQueryBuilder();

        if (!$applyFilters) {
            return $queryBuilder;
        }

        return $queryBuilder;
        foreach ($this->datasheet->getFilters() as $fieldName => $value) {
            if (!trim($value)) {
                continue;
            }

            if (strstr($fieldName, Datasheet::NEST_SEPARATOR)) {
                $parentAlias = $this->getQueryBuilderMainAlias();
                $path = explode(Datasheet::NEST_SEPARATOR, $fieldName);
                $targetFieldName = array_pop($path);

                foreach ($path as $relation) {
                    if (!$this->isAlreadyJoined($queryBuilder, $relation)) {
                        $queryBuilder->join(sprintf('%s.%s', $parentAlias, $relation), $relation);
                    }
                    $parentAlias = $relation;
                }
                $queryBuilder
                    ->andWhere(sprintf('%s.%s = :%sFilter', $parentAlias, $targetFieldName, $fieldName))
                    ->setParameter($fieldName . 'Filter', $value);
            } else {
                $queryBuilder
                    ->andWhere(sprintf('%s.%s = :%sFilter', $this->getQueryBuilderMainAlias(), $fieldName, $fieldName))
                    ->setParameter($fieldName . 'Filter', $value);
            }
        }

        return $queryBuilder;
    }

    protected function isAlreadyJoined($queryBuilder, $relation)
    {
        foreach ($queryBuilder->getDQLPart('join') as $joins) {
            /** @var Join $join */
            foreach ($joins as $join) {
                if ($join->getAlias() == $relation) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function getQueryBuilderMainAlias($queryBuilder): string
    {
        return $queryBuilder->getDQLPart('from')[0]->getAlias();
    }
}