<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\QueryBuilder;

class Datasheet
{
    /** @var QueryBuilder */
    protected $queryBuilder;

    protected $data = [];

    protected $fieldsToShow = [];

    protected $fieldsToRemove = [];

    protected $fieldOptions = [];

    protected $fieldHandlers = [];

    protected $translationPrefix;

    protected $summaryRow;

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

    public function showFields($fields): self
    {
        $fields = func_get_args();

        foreach ($fields as $field) {
            $this->fieldsToShow[StringUtility::toCamelCase($field)] = [
                'title' => StringUtility::normalize($field),
            ];
        }

        return $this;
    }

    public function removeFields(): self
    {
        $fields = func_get_args();

        foreach ($fields as $field) {
            $this->fieldsToRemove[] = StringUtility::toCamelCase($field);
        }

        return $this;
    }

    public function setFieldOptions($field, array $options): self
    {
        $this->fieldOptions[$field] = $options;

        return $this;
    }

    public function addFieldHandler($callbackFunction): self
    {
        $this->fieldHandlers[] = $callbackFunction;

        return $this;
    }

    public function setTranslationPrefix($translationPrefix): self
    {
        $this->translationPrefix = $translationPrefix;

        return $this;
    }

    public function setSummaryRow($summaryRow): self
    {
        $this->summaryRow = $summaryRow;

        return $this;
    }
}