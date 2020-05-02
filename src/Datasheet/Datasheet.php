<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Exception\DatasheetException;
use A2Global\CRMBundle\Exception\NotImplementedYetException;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Throwable;

class Datasheet
{
    const NEST_SEPARATOR = "___";

    use DatasheetDependencyInjectionTrait;

    use DatasheetFieldsTrait;

    use DatasheetGettersSettersTrait;

    protected $items = [];

    /**
     * Build executes when datasheet is rendered, when all parameters already defined.
     * That's is because we need to pass $limit, $perpage when executing callable getData()
     * These options are defined before build()
     */
    public function build()
    {
        if ($this->queryBuilder) {
            $this->buildDataFromQueryBuilder();
        } else {
            if ($this->getFilters()) {
                throw new NotImplementedYetException('Search doesnt work for array datasheets');
            }
            $this->buildDataFromArray();
        }
        $fieldsBuilt = false;
        $items = [];

        foreach ($this->data as $itemOriginal) {
            if (!$fieldsBuilt) {
                $this->buildFields($itemOriginal);
                $fieldsBuilt = true;
            }
            $item = [];

            foreach ($this->fields as $fieldName => $field) {
                $value = is_object($itemOriginal) ? $this->getObjectValue($itemOriginal, $fieldName) : $itemOriginal[$fieldName];
                $value = $this->handleValue($value);

                if (isset($this->fieldHandlers[$fieldName])) {
                    $callable = $this->fieldHandlers[$fieldName];

                    try {
                        $value = $callable($itemOriginal);
                    } catch (Throwable $e) {
                        throw new DatasheetException(sprintf('Datasheet failed to process handler for field `%s` with `%s`', $fieldName, $e->getMessage()));
                    }
                }
                $item[$fieldName] = $value;
            }
            $items[] = $item;
        }

        if (is_array($this->getSummaryRow())) {
            $item = [];

            foreach ($this->fields as $fieldName => $field) {
                $item[$fieldName] = $this->summaryRow[$fieldName] ?? '';
            }
            $items[] = $item;
        }

        $this->items = $items;
    }

    protected function getObjectValue($object, $path)
    {
        $path = explode('___', $path);
        $subObject = $object->{'get' . $path[0]}();

        if (count($path) == 1) {
            return $subObject;
        }
        array_shift($path);

        return $this->getObjectValue($subObject, implode('___', $path));
    }

    public function getFilterOptions()
    {
        if (!$this->queryBuilder) {
            return null;
        }
        $filterOptions = [];

        foreach ($this->fields as $fieldName => $field) {
            if (!$field['hasFilter']) {
                continue;
            }
            if (StringUtility::toCamelCase($fieldName) == 'id') {
                continue;
            }
            $filterOptions[$fieldName] = $this->getFieldFilterOptions($fieldName);
        }

        return $filterOptions;
    }

    public function getUniqueId()
    {
        return spl_object_id($this);
    }

    protected function buildDataFromArray()
    {
        if (is_callable($this->data)) {
            $callable = $this->data;
            $this->data = $callable($this->itemsPerPage, $this->page * $this->itemsPerPage);
        }

        if (is_null($this->itemsTotal)) {
            $this->setItemsTotal(count($this->data));
        }

        if (count($this->data) > $this->getItemsPerPage()) {
            $this->data = array_splice($this->data, $this->getPage() * $this->getItemsPerPage(), $this->getItemsPerPage());
        }
    }

    protected function buildDataFromQueryBuilder()
    {
        // Initialize main query builder
        if (is_callable($this->queryBuilder)) {
            $qbFunction = $this->queryBuilder;
            $this->queryBuilder = $qbFunction();
        }

        // Get & set total items count
        $total = $this->cloneQueryBuilder(true)
            ->select(sprintf('count(%s)', $this->getQueryBuilderMainAlias()))
            ->getQuery()
            ->getSingleScalarResult();
        $this->setItemsTotal($total);
//
        // Get items
        $offset = ($this->page) * $this->itemsPerPage;
        $this->data = $this->cloneQueryBuilder(true)
            ->setFirstResult($offset)
            ->setMaxResults($this->itemsPerPage)
            ->getQuery()
            ->getResult();

        // SQL query for debug
        $this->debug['sql'] = $this->cloneQueryBuilder(true)
            ->setFirstResult($offset)
            ->setMaxResults($this->itemsPerPage)
            ->getQuery()
            ->getSQL();
    }

    protected function getFieldFilterOptions($field)
    {
        if (strstr($field, self::NEST_SEPARATOR)) {
            $queryBuilder = $this->cloneQueryBuilder();
            $path = explode(self::NEST_SEPARATOR, $field);
            $field = array_pop($path);
            $parentAlias = $this->getQueryBuilderMainAlias();

            foreach ($path as $relation) {
                $queryBuilder->join(sprintf('%s.%s', $parentAlias, $relation), $relation);
                $parentAlias = $relation;
            }
            $result = $queryBuilder
                ->select(sprintf('DISTINCT(%s.%s)', $relation, $field))
                ->orderBy(sprintf('%s.%s', $relation, $field), 'ASC')
                ->getQuery()
                ->getArrayResult();
        } else {
            $result = $this->cloneQueryBuilder()
                ->select(sprintf('DISTINCT(%s.%s)', $this->getQueryBuilderMainAlias(), $field))
                ->orderBy(sprintf('%s.%s', $this->getQueryBuilderMainAlias(), $field), 'ASC')
                ->getQuery()
                ->getArrayResult();
        }
        return array_map(function ($item) {
            return reset($item);
        }, $result);
    }

    protected function cloneQueryBuilder($applyFilters = false): QueryBuilder
    {
        $queryBuilder = clone $this->queryBuilder;

        if (!$applyFilters) {
            return $queryBuilder;
        }

        foreach ($this->getFilters() as $fieldName => $value) {
            if (!trim($value)) {
                continue;
            }

            if (strstr($fieldName, self::NEST_SEPARATOR)) {
                $parentAlias = $this->getQueryBuilderMainAlias();
                $path = explode(self::NEST_SEPARATOR, $fieldName);
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

    protected function getQueryBuilderMainAlias(): string
    {
        return $this->queryBuilder->getDQLPart('from')[0]->getAlias();
    }

    protected function handleValue($value)
    {
        if (is_bool($value)) {
            if ($value) {
                return '<span class="badge bg-light-blue">Yes</span>';
            } else {
                return '<span class="badge">No</span>';
            }
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('d-m-Y');
        }

        if (is_object($value)) {
            if (!method_exists($value, '__toString')) {
                return StringUtility::normalize(StringUtility::getShortClassName($value)) . ' #' . $value->getId();
            } else {
                return (string)$value;
            }
        }

        return $value;
    }
}