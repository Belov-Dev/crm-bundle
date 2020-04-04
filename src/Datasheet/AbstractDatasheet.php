<?php

namespace A2Global\CRMBundle\Datasheet;

abstract class AbstractDatasheet implements DatasheetInterface
{
    protected $fields = [];

    protected $items = [];

    protected $itemsTotal = [];

    protected $actionsTemplate = null;

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItemsTotal($total)
    {
        $this->itemsTotal = $total;
    }

    public function getItemsTotal()
    {
        return $this->itemsTotal;
    }

    public function getActionsTemplate()
    {
        return $this->actionsTemplate;
    }
}