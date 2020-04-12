<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTimeInterface;

class Datasheet
{
    protected $fields = [];

    protected $items = [];

    protected $page = 0;

    protected $itemsPerPage = 20;

    protected $entityInfoProvider;

    public function __construct(
        EntityInfoProvider $entityInfoProvider
    )
    {
        $this->entityInfoProvider = $entityInfoProvider;
    }

    public function setData($dataFunction): self
    {
        $items = $dataFunction($this->itemsPerPage, $this->page * $this->itemsPerPage);

        if (!count($items)) {
            $this->items = [];
        }

        foreach ($items as $itemOriginal) {
            if (!$this->fields) {
                $this->buildFields($itemOriginal);
            }
            $item = [];

            foreach ($this->fields as $fieldName => $field) {
                $value = is_object($itemOriginal) ? $itemOriginal->{'get' . $fieldName}() : $itemOriginal[$fieldName];
                $item[$fieldName] = $this->handleValue($value);
            }
            $this->items[] = $item;
        }

        return $this;
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getActionsTemplate()
    {
        return '';
    }

    public function getActionTemplate()
    {
        return '';
    }

    public function getFields()
    {
        if (!$this->fields) {
            $this->buildFields();
        }

        return $this->fields;
    }

    public function addField($name, $title): self
    {
        $this->fields[$name] = [
            'title' => $title,
            'hasFiltering' => false, //in_array($key, $this->hasFilter),
        ];

        return $this;
    }

    public function removeFields(): self
    {
        $this->fields = [];

        return $this;
    }

    public function removeField($name): self
    {
        unset($this->fields[StringUtility::toCamelCase($name)]);

        return $this;
    }

    protected function buildFields($item)
    {
        if (is_object($item)) {
            $entity = $this->entityInfoProvider->getEntity($item);

            foreach ($entity->getFields() as $field) {
                $this->fields[StringUtility::toCamelCase($field->getName())] = [
                    'title' => $field->getName(),
                    'hasFiltering' => false, //in_array($key, $this->hasFilter),
                    // todo !
                ];
            }
        } else {
            foreach ($item as $key => $value) {
                $this->fields[$key] = [
                    'title' => StringUtility::normalize($key),
                    'hasFiltering' => false, //in_array($key, $this->hasFilter),
                    // todo !
                ];
            }
        }
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
            return $value->format('j/m/Y');
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