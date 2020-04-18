<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Exception\DatasheetException;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;
use Throwable;

class Datasheet
{
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
            $this->setEnableFiltering(true);
        } else {
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
                $value = is_object($itemOriginal) ? $itemOriginal->{'get' . $fieldName}() : $itemOriginal[$fieldName];
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
        $this->items = $items;
    }

    public function getFilterOptions()
    {
        if (!$this->queryBuilder) {
            return null;
        }
        $filterOptions = [];

        foreach ($this->fields as $fieldName => $field) {
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

    protected function getFieldFilterOptions($field)
    {
        $result = $this->cloneQueryBuilder()
            ->select(sprintf('DISTINCT(%s.%s)', $this->getQueryBuilderMainAlias(), $field))
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            return reset($item);
        }, $result);
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
        $total = $this->cloneQueryBuilder()
            ->select(sprintf('count(%s)', $this->getQueryBuilderMainAlias()))
            ->getQuery()
            ->getSingleScalarResult();
        $this->setItemsTotal($total);

        // Get items
        $offset = ($this->page) * $this->itemsPerPage;
        $this->data = $this->cloneQueryBuilder()
            ->setFirstResult($offset)
            ->setMaxResults($this->itemsPerPage)
            ->getQuery()
            ->getResult();
    }

    protected function cloneQueryBuilder(): QueryBuilder
    {
        return clone $this->queryBuilder;
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