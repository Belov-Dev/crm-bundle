<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\QueryBuilder;

class sqlDatasheetBuilder extends AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    public function supports(): bool
    {
        return
            $this->datasheet->getQueryBuilder()
            &&
            count($this->datasheet->getQueryBuilder()->getDQLPart('select')) > 1;
    }

    public function getItems(): array
    {
        $query = $this
            ->cloneQbFiltered()
            ->setFirstResult($this->datasheet->getPage() * $this->datasheet->getItemsPerPage())
            ->setMaxResults($this->datasheet->getItemsPerPage())
            ->getQuery()
            ->getSQL();
//        echo $query;

        $items = $this
            ->cloneQbFiltered()
            ->setFirstResult($this->datasheet->getPage() * $this->datasheet->getItemsPerPage())
            ->setMaxResults($this->datasheet->getItemsPerPage())
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

    public function getItemsTotal(): int
    {
        return $this
            ->cloneQbFiltered()
            ->select(sprintf('count(%s)', $this->getQbMainAlias()))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getFields(): array
    {
        $fields = [];

        foreach ($this->datasheet->getQueryBuilder()->getDQLPart('select') as $select) {
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

    public function hasFilters(): bool
    {
        return false;
    }

    protected function cloneQbFiltered(): QueryBuilder
    {
        return $this->cloneQb();
        $queryBuilder = clone $this->datasheet->getQueryBuilder();

        if (!$applyFilters) {
            return $queryBuilder;
        }

        return $queryBuilder;
        foreach ($datasheet->getFilters() as $fieldName => $value) {
            if (!trim($value)) {
                continue;
            }

            if (strstr($fieldName, DatasheetExtended::NEST_SEPARATOR)) {
                $parentAlias = $this->getQueryBuilderMainAlias();
                $path = explode(DatasheetExtended::NEST_SEPARATOR, $fieldName);
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
                    ->andWhere(sprintf(
                        '%s.%s = :%sFilter',
                        $this->getQueryBuilderMainAlias($datasheet->getQueryBuilder()),
                        $fieldName,
                        $fieldName
                    ))
                    ->setParameter($fieldName . 'Filter', $value);
            }
        }

        return $queryBuilder;
    }
}