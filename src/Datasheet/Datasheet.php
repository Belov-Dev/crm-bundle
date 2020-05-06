<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\QueryBuilder;

class Datasheet
{
    /** @var QueryBuilder */
    public $queryBuilder;

    public $data = [];

    public $fields = [];

    public $fieldsToRemove = [];

    public $fieldHandlers = [];

    public $translationPrefix;

    public $summaryRow;

    public function setQueryBuilder($queryBuilder): self
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setFields($fields): self
    {
        $fields = func_get_args();

        foreach ($fields as $field) {
            $this->fields[StringUtility::toCamelCase($field)] = [];
        }

        return $this;
    }

    public function removeFields()
    {
        $fields = func_get_args();

        foreach ($fields as $field) {
            $this->fieldsToRemove[] = StringUtility::toCamelCase($field);
        }
    }

    public function addFieldHandler($callbackFunction)
    {
        $this->fieldHandlers[] = $callbackFunction;
    }

    public function setTranslationPrefix($translationPrefix)
    {
        $this->translationPrefix = $translationPrefix;
    }

    public function setSummaryRow($summaryRow)
    {
        $this->summaryRow = $summaryRow;
    }
}