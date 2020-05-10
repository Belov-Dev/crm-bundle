<?php

namespace A2Global\CRMBundle\Datasheet;

use Doctrine\ORM\QueryBuilder;

class DatasheetExtended extends Datasheet
{
    const NEST_SEPARATOR = "___";

    protected $fields;

    protected $items;

    protected $itemsTotal;

    protected $hasFilters;

    protected $page;

    protected $itemsPerPage;

    protected $filters = [];

    public function __construct($data)
    {
        parent::__construct($data['data']);
    }

    /** Base properties */

    public function getData()
    {
        return $this->data;
    }

    public function getQueryBuilder(): ?QueryBuilder
    {
        return $this->queryBuilder;
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
        return $this->page;
    }

    public function setPage($page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    public function setItemsPerPage($itemsPerPage): self
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    public function getItemsTotal()
    {
        return $this->itemsTotal;
    }

    public function setItemsTotal($itemsTotal): self
    {
        $this->itemsTotal = $itemsTotal;

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