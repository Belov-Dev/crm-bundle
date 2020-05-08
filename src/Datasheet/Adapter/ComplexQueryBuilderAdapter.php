<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\DatasheetExtended;
use A2Global\CRMBundle\Utility\StringUtility;

class ComplexQueryBuilderAdapter implements DatasheetAdapterInterface
{
    use QueryBuilderAdapterTrait;

    public function supports(DatasheetExtended $datasheet): bool
    {
        return $datasheet->getQueryBuilder() && count($datasheet->getQueryBuilder()->getDQLPart('select')) > 1;
    }

    public function getItems(DatasheetExtended $datasheet): array
    {
        $query = $this
            ->cloneQueryBuilder($datasheet, true)
            ->setFirstResult($datasheet->getPage() * $datasheet->getItemsPerPage())
            ->setMaxResults($datasheet->getItemsPerPage())
            ->getQuery()
            ->getSQL();
//        echo $query;

        $items = $this
            ->cloneQueryBuilder($datasheet, true)
            ->setFirstResult($datasheet->getPage() * $datasheet->getItemsPerPage())
            ->setMaxResults($datasheet->getItemsPerPage())
            ->getQuery()
            ->getArrayResult();

        $items = array_map(function ($item) {
            if (isset($item[0])) {
                unset($item[0]);
            }

            return $item;
        }, $items);

        return $items;
    }

    public function getItemsTotal(DatasheetExtended $datasheet): int
    {
        return $this->cloneQueryBuilder($datasheet, true)
            ->select(sprintf('count(%s)', $this->getQueryBuilderMainAlias($datasheet->getQueryBuilder())))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getFields(DatasheetExtended $datasheet): array
    {
        $fields = [];

        foreach ($datasheet->getQueryBuilder()->getDQLPart('select') as $select) {
            $field = $select->getParts()[0];
            $tmp = explode('.', $field);

            if (count($tmp) < 2) {
                continue;
            }
            $fields[StringUtility::toCamelCase(trim($tmp[1]))] = [
                'title' => StringUtility::normalize($tmp[1]),
            ];
        }

        return $fields;
    }

    public function hasFilters(DatasheetExtended $datasheet): bool
    {
        return false;
    }
}