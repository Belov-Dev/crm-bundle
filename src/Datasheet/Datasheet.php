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
            $this->buildDataFromQb();
        }else {
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

    protected function buildDataFromQb()
    {
        $qbFunction = $this->queryBuilder;
        /** @var QueryBuilder $qb */
        $qb = $qbFunction();
        /** @var From $firstFrom */
        $firstFrom = $qb->getDQLPart('from')[0];
        $mainAlias = $firstFrom->getAlias();
        $total = (clone $qb)
            ->select('count(' . $mainAlias . ')')
            ->getQuery()
            ->getSingleScalarResult();
        $this->setItemsTotal($total);
        $offset = ($this->page) * $this->itemsPerPage;
        $this->data = $qb
            ->setFirstResult($offset)
            ->setMaxResults($this->itemsPerPage)
            ->getQuery()
            ->getResult();
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