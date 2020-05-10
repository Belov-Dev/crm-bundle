<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Utility\StringUtility;

class Datasheet
{
    protected $data;

    protected $fieldsToShow = [];

    protected $fieldsToRemove = [];

    protected $fieldOptions = [];

    protected $fieldHandlers = [];

    protected $translationPrefix;

    protected $summaryRow;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function __invoke(): array
    {
        return [
            'original' => $this,
            'data' => $this->data,
            'fieldsToShow' => $this->fieldsToShow,
            'fieldsToRemove' => $this->fieldsToRemove,
            'fieldOptions' => $this->fieldOptions,
            'fieldHandlers' => $this->fieldHandlers,
            'translationPrefix' => $this->translationPrefix,
            'summaryRow' => $this->summaryRow,
        ];
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