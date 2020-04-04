<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Utility\StringUtility;

abstract class ArrayDatasheet extends AbstractDatasheet
{
    protected $hasFilter = [];

    public function buildFields()
    {
        $item = reset($this->items);
        $fields = [];

        foreach ($item as $key => $value) {
            $fields[$key] = [
                'title' => StringUtility::normalize($key),
                'hasFiltering' => in_array($key, $this->hasFilter),
            ];
        }

        $this->setFields($fields);
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
        $this->setItemsTotal(count($this->items));
    }
}