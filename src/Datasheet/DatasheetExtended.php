<?php

namespace A2Global\CRMBundle\Datasheet;

use Doctrine\ORM\QueryBuilder;

class DatasheetExtended extends Datasheet
{
    protected $fields;

    protected $items;

    protected $itemsTotal;

    protected $hasFilters;

    protected $page;

    protected $itemsPerPage;

    protected $filters;

    public function getUniqueId()
    {
        return spl_object_id($this);
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

    public function setHasFilters($hasFilters)
    {
        $this->hasFilters = $hasFilters;
        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
        return $this;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getFieldsToShow(): array
    {
        return $this->fieldsToShow;
    }

    public function getFieldsToRemove(): array
    {
        return $this->fieldsToRemove;
    }

    public function getFieldHandlers(): array
    {
        return $this->fieldHandlers;
    }
}