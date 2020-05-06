<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Provider\EntityInfoProvider;
use Doctrine\ORM\QueryBuilder;

trait DatasheetDependencyInjectionTrait
{








    use DatasheetFieldsTrait;

    use DatasheetGettersSettersTrait;

    protected $items = [];

    /** new getters setters */

    // todo: remove enable filtering
    public function setQueryBuilder($queryBuilder): self
    {
        $this->setEnableFiltering(true);
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    /** new getters setters end */


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
            $qb = $this->cloneQueryBuilder()
                ->select(sprintf('DISTINCT(%s.%s)', $this->getQueryBuilderMainAlias(), $field))
                ->orderBy(sprintf('%s.%s', $this->getQueryBuilderMainAlias(), $field), 'ASC');
            $sql = (clone $qb)->getQuery()->getSQL();
            $result = (clone $qb)->getQuery()->getArrayResult();
        }
        return array_map(function ($item) {
            return reset($item);
        }, $result);
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
    protected $entityInfoProvider;

    protected $itemsSourceCallable;

    public function __construct(
        EntityInfoProvider $entityInfoProvider
    )
    {
        $this->entityInfoProvider = $entityInfoProvider;
    }
}