<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Datasheet\Adapter\DatasheetAdapterInterface;
use A2Global\CRMBundle\Registry\DatasheetAdapterRegistry;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;
use Exception;

class DatasheetBuilder
{
    const NEST_SEPARATOR = "___";

    protected $adapterRegistry;

    public function __construct(
        DatasheetAdapterRegistry $adapterRegistry
    )
    {
        $this->adapterRegistry = $adapterRegistry;
    }

    public function build(DatasheetExtended $datasheet)
    {
        $adapter = $this->getAdapter($datasheet);
        $datasheet
            ->setItems($adapter->getItems($datasheet))
            ->setItemsTotal($adapter->getItemsTotal($datasheet));

        $fields = $datasheet->getFieldsToShow() ?: $adapter->getFields($datasheet);

        foreach ($datasheet->getFieldsToRemove() as $fieldToRemove) {
            if (isset($fields[$fieldToRemove])) {
                unset($fields[$fieldToRemove]);
            }
        }
        $datasheet->setFields($fields);
        $this->updateItems($datasheet);
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

    /** move */

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

    protected function updateItems(DatasheetExtended $datasheet)
    {
        $items = [];

        foreach ($datasheet->getItems() as $itemOriginal) {
            $item = [];

            foreach ($datasheet->getFields() as $fieldName => $fieldOptions) {
//                if (!isset($itemOriginal[$fieldName])) {
//                    throw new DatasheetException(sprintf('Datasheet failed to get %s value from data', $fieldName));
//                }
//                $value = $itemOriginal[$fieldName];
                $value = is_object($itemOriginal) ? $this->getObjectValue($itemOriginal, $fieldName) : $itemOriginal[$fieldName];
                $value = $this->handleValue($value);

//                if (isset($this->datasheet->fieldHandlers[$fieldName])) {
//                    $callable = $this->datasheet->fieldHandlers[$fieldName];
//
//                    try {
//                        $value = $callable($itemOriginal);
//                    } catch (Throwable $e) {
//                        throw new DatasheetException(sprintf('Datasheet failed to process handler for field `%s` with `%s`', $fieldName, $e->getMessage()));
//                    }
//                }
                $item[$fieldName] = $value;
            }
            $items[] = $item;
        }
        $datasheet->setItems($items);
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

}