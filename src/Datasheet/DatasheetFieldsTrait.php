<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Component\Field\RelationField;
use A2Global\CRMBundle\Utility\StringUtility;

trait DatasheetFieldsTrait
{
    protected $fields = [];

    protected $fieldHandlers;

    protected $fieldsToAdd = [];

    protected $fieldsToRemove = [];

    protected $removeFields = false;

    public function setFields($fields): self
    {
        $this->removeFields();
        $fields = func_get_args();

        foreach ($fields as $field) {
            $this->setField($field);
        }

        return $this;
    }

    public function setField($name, $title = null, $hasFilter = null): self
    {
        $name = str_replace('.', '___', $name);
        $this->fieldsToAdd[] = [$name, $title, is_null($hasFilter) ? $this->isEnableFiltering() : $hasFilter];

        return $this;
    }

    public function addField($name, $title = null, $hasFilter = null): self
    {
        return $this->setField($name, $title, $hasFilter);
    }

    public function removeFields(): self
    {
        $fields = func_get_args();

        if ($fields) {
            foreach ($fields as $field) {
                $this->fieldsToRemove[] = $field;
            }
        } else {
            $this->removeFields = true;
        }

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
        // Build fields from data
        if (!$this->removeFields) {
            if (is_object($item)) {
                $this->buildFieldsFromObjectItem($item);
            } else {
                $this->buildFieldsFromArrayItem($item);
            }
        }

        // Remove specified fields
        foreach ($this->fieldsToRemove as $name) {
            unset($this->fields[$name]);
        }

        // Add specified fields
        foreach ($this->fieldsToAdd as $field) {
            $this->fields[$field[0]] = [
                'title' => StringUtility::normalize($field[1] ?: $field[0]),
                'hasFilter' => $field[2],
            ];
        }
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
            if(0 === $name){
                continue;
            }
            $this->fields[$name] = [
                'title' => StringUtility::normalize($name),
                'hasFilter' => $this->isEnableFiltering(),
            ];
        }
    }
}