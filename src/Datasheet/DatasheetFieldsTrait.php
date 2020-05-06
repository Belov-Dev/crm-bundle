<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Component\Field\RelationField;
use A2Global\CRMBundle\Utility\StringUtility;

trait DatasheetFieldsTrait
{
    public $fields = [];

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

    public function getFieldsToAdd(): array
    {
        return $this->fieldsToAdd;
    }

    public function getFieldsToRemove(): array
    {
        return $this->fieldsToRemove;
    }
}