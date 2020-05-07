<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Datasheet\Adapter\DatasheetAdapterInterface;
use A2Global\CRMBundle\Exception\DatasheetException;
use A2Global\CRMBundle\Registry\DatasheetAdapterRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Exception;
use PHPUnit\Framework\Error\Deprecated;
use Throwable;

class DatasheetBuilder
{
    const NEST_SEPARATOR = "___";

    protected $adapterRegistry;

    /** @var Datasheet */
    protected $datasheet;

    protected $adapter;

    protected $fields;

    protected $items;

    protected $page;

    protected $perPage;

    protected $filters;

    protected $itemsTotal;

    protected $hasFilters;

    public function __construct(
        DatasheetAdapterRegistry $adapterRegistry
    )
    {
        $this->adapterRegistry = $adapterRegistry;
    }

    /**
     * Build executes when datasheet is rendered, when all parameters already defined.
     * That's is because we need to pass $limit, $perpage when executing callable getData()
     * These options are defined before build()
     */
    public function build(Datasheet $datasheet, $page = 1, $perPage = 15, $filters = [])
    {
        $this->datasheet = $datasheet;
        $this->page = $page - 1;
        $this->perPage = $perPage;
        $this->filters = $filters;

        $this->adapter = $this->getAdapter($datasheet);
        $this->items = $this->adapter->buildItems($datasheet, $this->page, $this->perPage, $filters);
        $this->fields = $this->adapter->buildFields($datasheet);
        $this->itemsTotal = $this->adapter->buildItemsTotal($datasheet);
        $this->updateItems();
    }

    protected function getAdapter($datasheet): DatasheetAdapterInterface
    {
        foreach ($this->adapterRegistry->get() as $adapter) {
            if ($adapter->supports($datasheet)) {
                return $adapter;
            }
        }

        throw new Exception('Datsheet adapter can not be resolved');
    }

    /** Build data */

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
        // Do we still need this?
        if (is_callable($this->datasheet->queryBuilder)) {
            throw new Deprecated('User another method');
        }

        // Get & set total items count
//        $total = $this->cloneQueryBuilder(true)
//            ->select(sprintf('count(%s)', $this->getQueryBuilderMainAlias()))
//            ->getQuery()
//            ->getSQL();
//            ->getSingleScalarResult();
//        $this->itemsTotal = $total;
//
        // Get items
        $query = $this->cloneQueryBuilder(true)
            ->setFirstResult($this->page * $this->perPage)
            ->setMaxResults($this->perPage)
            ->getQuery()
            ->getSQL();
        echo $query;
        $items = $this->cloneQueryBuilder(true)
            ->setFirstResult($this->page * $this->perPage)
            ->setMaxResults($this->perPage)
            ->getQuery()
            ->getArrayResult();
        // In case of complex query builder — result would be an array
        // and each item has extra key '0', needed to be deleted
        $this->items = $items;
    }

    /** Build fields */

    protected function buildFields()
    {
        if ($this->datasheet->fields) {
            $this->fields = $this->datasheet->fields;

            return;
        }
        $fields = [];

        foreach ($this->fieldsResolverRegistry->get() as $strategy) {
            if ($strategy->supports($this->datasheet)) {
                $fields = $strategy->getFields($this->datasheet);

                break;
            }
        }

        foreach ($this->datasheet->fieldsToRemove as $field) {
            unset($fields[$field]);
        }
        $this->fields = $fields;
    }

    protected function buildFieldsFromObjectItem($item)
    {
        $entity = $this->entityInfoProvider->getEntity($item);

        foreach ($entity->getFields() as $field) {
            $this->fields[StringUtility::toCamelCase($field->getName())] = [
                'title' => $field->getName(),
                'hasFilter' => $this->isEnableFiltering() && (!$field instanceof RelationField),
            ];
        }
    }

    protected function buildFieldsFromArrayItem($item)
    {
        foreach (array_keys($item) as $name) {
            if (0 === $name) {
                continue;
            }
            $this->fields[$name] = [
                'title' => StringUtility::normalize($name),
                'hasFilter' => $this->isEnableFiltering(),
            ];
        }
    }

    /** Update data */

    protected function updateItems()
    {
        $items = [];

        foreach ($this->items as $itemOriginal) {
            $item = [];

            foreach ($this->fields as $fieldName => $fieldOptions) {
//                if (!isset($itemOriginal[$fieldName])) {
//                    throw new DatasheetException(sprintf('Datasheet failed to get %s value from data', $fieldName));
//                }
//                $value = $itemOriginal[$fieldName];
                $value = is_object($itemOriginal) ? $this->getObjectValue($itemOriginal, $fieldName) : $itemOriginal[$fieldName];
                $value = $this->handleValue($value);

                if (isset($this->datasheet->fieldHandlers[$fieldName])) {
                    $callable = $this->datasheet->fieldHandlers[$fieldName];

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

    protected function getObjectValue($object, $path)
    {
        $path = explode(self::NEST_SEPARATOR, $path);
        $subObject = $object->{'get' . $path[0]}();

        if (count($path) == 1) {
            return $subObject;
        }
        array_shift($path);

        return $this->getObjectValue($subObject, implode(self::NEST_SEPARATOR, $path));
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

    /** Getters for the resulted datasheet todo move to another class */

    public function getFields()
    {
        return $this->fields;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getItemsTotal()
    {
        return $this->itemsTotal;
    }

    public function getUniqueId()
    {
        return spl_object_id($this);
    }

    public function hasFilters()
    {
        return $this->hasFilters;
    }

    public function translationPrefix()
    {
        return $this->datasheet->translationPrefix;
    }

    public function getSummaryRow()
    {
        return $this->datasheet->summaryRow;
    }

    /** Other */

}