<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Utility\StringUtility;

trait DatasheetFieldsTrait
{
    protected $fields = [];

    protected $fieldHandlers;

    protected $fieldsToAdd = [];

    protected $fieldsToRemove = [];

    protected $removeFields = false;

    public function setField($name, $title = null): self
    {
        $this->fieldsToAdd[] = [$name, $title];

        return $this;
    }

    public function addField($name, $title = null): self
    {
        return $this->setField($name, $title);
    }

    public function removeFields(): self
    {
        $this->removeFields = true;

        return $this;
    }

    public function removeField($name): self
    {
        $this->fieldsToRemove[] = $name;

        return $this;
    }

    public function addFieldHandler($field, $callable): self
    {
        $this->fieldHandlers[$field] = $callable;

        return $this;
    }

    protected function buildFields($item)
    {
        if (!$this->removeFields) {
            if (is_object($item)) {
                $this->buildFieldsFromObjectItem($item);
            } else {
                $this->buildFieldsFromArrayItem($item);
            }
        }

        foreach ($this->fieldsToRemove as $name) {
            unset($this->fields[StringUtility::toCamelCase($name)]);
        }

        foreach ($this->fieldsToAdd as $field) {
            $this->fields[StringUtility::toCamelCase($field[0])] = [
                'title' => StringUtility::normalize($field[1] ?: $field[0]),
                'hasFiltering' => false,
            ];
        }
    }

    protected function buildFieldsFromObjectItem($item)
    {
        $entity = $this->entityInfoProvider->getEntity($item);

        foreach ($entity->getFields() as $field) {
            $this->fields[StringUtility::toCamelCase($field->getName())] = [
                'title' => $field->getName(),
                'hasFiltering' => false, //in_array($key, $this->hasFilter),
                // todo !
            ];
        }
    }

    protected function buildFieldsFromArrayItem($item)
    {
        foreach (array_keys($item) as $name) {
            $this->fields[StringUtility::toCamelCase($name)] = [
                'title' => StringUtility::normalize($name),
                'hasFiltering' => false, //in_array($key, $this->hasFilter),
                // todo !
            ];
        }
    }
}