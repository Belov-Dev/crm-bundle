<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Utility\StringUtility;

abstract class ArrayDatasheet extends AbstractDatasheet
{
    protected $hasFilter = [];

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function buildFields()
    {
        $item = reset($this->items);

        foreach ($item as $key => $value) {
            $this->fields[$key] = [
                'title' => StringUtility::normalize($key),
                'hasFiltering' => in_array($key, $this->hasFilter),
            ];
        }
    }

    public function applyFilters($filters = [])
    {
        foreach ($this->items as $key => $item) {
            foreach ($filters as $field => $searchString) {
                if (!trim($searchString)) {
                    continue;
                }

                if ($field == 'id') {
                    if ($item[$field] != $searchString) {
                        unset($this->items[$key]);
                    }
                    continue;
                }

                if (!stristr($item[$field], $searchString)) {
                    unset($this->items[$key]);
                }
            }
        }
    }

    public function applyPagination($startFrom, $limit)
    {
        $this->items = array_splice($this->items, $startFrom, $limit);
    }

    public function buildItemsTotal()
    {
        $this->itemsTotal = count($this->items);
    }
}