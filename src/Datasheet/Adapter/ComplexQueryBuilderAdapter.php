<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\Datasheet;
use A2Global\CRMBundle\Utility\StringUtility;

class ComplexQueryBuilderAdapter implements DatasheetAdapterInterface
{
    use QueryBuilderAdapterTrait;

    public function supports(Datasheet $datasheet): bool
    {
        return count($datasheet->queryBuilder->getDQLPart('select')) > 1;
    }

    public function buildItems(Datasheet $datasheet, $page = 1, $perPage = 15, $filters = []): array
    {
        $query = $this
            ->cloneQueryBuilder($datasheet->queryBuilder, true)
            ->setFirstResult($page * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getSQL();
//        echo $query;

        $items = $this->cloneQueryBuilder($datasheet->queryBuilder, true)
            ->setFirstResult($page * $perPage)
            ->setMaxResults($perPage)
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

    public function buildFields(Datasheet $datasheet): array
    {
        $fields = [];

        foreach ($datasheet->queryBuilder->getDQLPart('select') as $select) {
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

    public function buildItemsTotal(Datasheet $datasheet): int
    {
        return $this->cloneQueryBuilder($datasheet->queryBuilder, true)
            ->select(sprintf('count(%s)', $this->getQueryBuilderMainAlias($datasheet->queryBuilder)))
            ->getQuery()
            ->getSingleScalarResult();
    }
}