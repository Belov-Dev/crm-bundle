<?php

namespace A2Global\CRMBundle\Datasheet;

class DatasheetExtended extends Datasheet
{
    const NEST_SEPARATOR = "___";

    protected $original;

    protected $fields = [];

    protected $items = [];

    protected $hasFilters = false;

    protected $filters = [];

    public function __construct($data)
    {
        parent::__construct(null);

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /** Base properties */

    public function getOriginal(): Datasheet
    {
        return $this->original;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getFieldsToShow(): ?array
    {
        return $this->fieldsToShow;
    }

    public function getFieldsToRemove(): ?array
    {
        return $this->fieldsToRemove;
    }

    public function getFieldHandlers(): ?array
    {
        return $this->fieldHandlers;
    }

    /** Extended properties */

    public function getUniqueId()
    {
        $uniqueData = [
            microtime(),
//            $this->getData(),
//            $this->getQueryBuilder(),
//            $this->getFieldsToShow(),
        ];

        return strtoupper(substr(base_convert(md5(json_encode($uniqueData)), 16, 32), 0, 12));
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items): self
    {
        $this->items = $items;

        return $this;
    }

    public function getPage()
    {
        return $this->getOriginal()->page;
    }

    public function setPage($page): self
    {
        $this->getOriginal()->page = $page;

        return $this;
    }

    public function getItemsPerPage()
    {
        return $this->getOriginal()->itemsPerPage;
    }

    public function setItemsPerPage($itemsPerPage): self
    {
        $this->getOriginal()->itemsPerPage = $itemsPerPage;

        return $this;
    }

    public function getItemsTotal()
    {
        return $this->getOriginal()->itemsTotal;
    }

    public function setItemsTotal($itemsTotal): self
    {
        $this->getOriginal()->itemsTotal = $itemsTotal;

        return $this;
    }

    public function setHasFilters($hasFilters): self
    {
        $this->hasFilters = $hasFilters;

        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters($filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function getFieldOptions($fieldName)
    {
        return $this->fieldOptions[$fieldName] ?? [];
    }

    /** Other */

    public function hasFilters()
    {
        return $this->hasFilters;
    }

    public function getTranslationPrefix()
    {
        return $this->translationPrefix;
    }

    public function getSummaryRow()
    {
        return $this->summaryRow;
    }

    public function getHasFilters()
    {
        return $this->hasFilters;
    }

}