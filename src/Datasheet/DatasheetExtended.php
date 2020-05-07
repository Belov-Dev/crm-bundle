<?php

namespace A2Global\CRMBundle\Datasheet;

class DatasheetExtended extends Datasheet
{
    protected $fields;

    protected $items;

    protected $itemsTotal;

    protected $hasFilters;

    public function getFields()
    {
        return $this->fields;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getItemsTotal()
    {
        return $this->itemsTotal;
    }

    public function getUniqueId()
    {
        return spl_object_id($this);
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
}