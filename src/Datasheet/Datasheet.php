<?php

namespace A2Global\CRMBundle\Datasheet;

class Datasheet
{
    protected $data;

    protected $fieldsToShow = [];

    protected $fieldsToRemove = [];

    protected $fieldOptions = [];

    protected $fieldHandlers = [];

    protected $translationPrefix;

    protected $summaryRow;

    protected $page = 1;

    protected $itemsPerPage = 15;

    protected $itemsTotal = 0;

    protected $constructedFrom = '';

    public function __construct($data)
    {
        $this->data = $data;
        $backtrace = debug_backtrace();
        $lastBacktrace = reset($backtrace);
        $this->constructedFrom = $lastBacktrace['file'] . $lastBacktrace['line'];
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
            'page' => $this->page,
            'itemsPerPage' => $this->itemsPerPage,
            'itemsTotal' => $this->itemsTotal,
            'constructedFrom' => $this->constructedFrom,
        ];
    }

    public function showFields($fields): self
    {
        $this->fieldsToShow = func_get_args();

        return $this;
    }

    public function removeFields(): self
    {
        $this->fieldsToRemove = func_get_args();

        return $this;
    }

    public function setFieldOptions($field, array $options): self
    {
        $this->fieldOptions[$field] = $options;

        return $this;
    }

    public function addFieldHandler($field, $callbackFunction): self
    {
        $this->fieldHandlers[$field] = $callbackFunction;

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

    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function setItemsPerPage(int $itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    public function setItemsTotal(int $itemsTotal): self
    {
        $this->itemsTotal = $itemsTotal;

        return $this;
    }
}