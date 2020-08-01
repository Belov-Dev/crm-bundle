<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Utility\StringUtility;

class ArrayDatasheetBuilder extends AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    public function supports(): bool
    {
        return is_array($this->getDatasheet()->getData()) || is_callable($this->getDatasheet()->getData());
    }

    public function build($page = null, $itemsPerPage = null, $filters = [], $sorting = [])
    {
        $isDataSourceCallable = false;

        if ($page) {
            $this->getDatasheet()->setPage($page);
        }

        if ($itemsPerPage) {
            $this->getDatasheet()->setItemsPerPage($itemsPerPage);
        }

        if (is_callable($this->getDatasheet()->getData())) {
            $callable = $this->getDatasheet()->getData();
            $this->getDatasheet()->setItems(
                $callable(
                    $this->getDatasheet()->getItemsPerPage(),
                    $this->getDatasheet()->getItemsPerPage() * ($this->getDatasheet()->getPage() - 1),
                    $filters
                )
            );
            $isDataSourceCallable = true;
        } else {
            $this->getDatasheet()->setItems($this->getDatasheet()->getData());
            $this->getDatasheet()->enableSorting();
        }

        if (!$this->getDatasheet()->getItemsTotal() && count($this->getDatasheet()->getItems())) {
            $this->getDatasheet()->setItemsTotal(count($this->getDatasheet()->getItems()));
        }
        $fields = [];

        if ($this->getDatasheet()->getFieldsToShow()) {
            foreach ($this->getDatasheet()->getFieldsToShow() as $fieldName) {
                $fieldName = StringUtility::toCamelCase($fieldName);
                $fields[$fieldName] = $this->getDatasheet()->getFieldOptions()[$fieldName] ?? [];
                $fields[$fieldName]['title'] = $this->getDatasheet()->getFieldOptions()[$fieldName]['title'] ?? StringUtility::normalize($fieldName);
                $fields[$fieldName]['hasFilter'] = false;
            }
        } else {
            if ($this->datasheet->getItems() && count($this->datasheet->getItems()) > 0) {
                foreach (array_keys($this->datasheet->getItems()[0]) as $fieldName) {
                    $fieldName = StringUtility::toCamelCase($fieldName);
                    $fields[$fieldName] = $this->getDatasheet()->getFieldOptions()[$fieldName] ?? [];
                    $fields[$fieldName]['title'] = $fields[$fieldName]['title'] ?? StringUtility::normalize($fieldName);
                    $fields[$fieldName]['hasFilter'] = false;
                }
            }
        }

        foreach ($this->getDatasheet()->getFieldsToRemove() as $fieldToRemove) {
            unset($fields[$fieldToRemove]);
        }
        $this->getDatasheet()->setFields($fields);

        if (!$isDataSourceCallable && !$this->getDatasheet()->isFiltersDisabled()) {
            $this->addFilterChoices();
            $this->getDatasheet()->setFilters($filters);
            $this->applyFilters();
        }

        if (!$isDataSourceCallable) {
            $items = $this->getDatasheet()->getItems();
            $items = array_splice(
                $items,
                $this->getDatasheet()->getItemsPerPage() * ($this->getDatasheet()->getPage() - 1),
                $this->getDatasheet()->getItemsPerPage()
            );
            $this->getDatasheet()->setItems($items);
            $this->applySorting($sorting);
        }

        parent::build($page, $itemsPerPage, $filters);
    }

    protected function applyFilters()
    {
        $filters = array_filter($this->getDatasheet()->getFilters(), function ($filter) {
            return !empty(trim($filter));
        });
        $this->getDatasheet()->setFilters($filters);

        if (count($filters) < 1) {
            return;
        }
        $filteredItems = [];

        foreach ($this->getDatasheet()->getItems() as $item) {
            $addItem = true;

            foreach ($filters as $name => $value) {
                if ($item[$name] != $value) {
                    $addItem = false;
                }
            }

            if ($addItem) {
                $filteredItems[] = $item;
            }
        }

        $this->getDatasheet()->setItemsTotal(count($filteredItems));
        $this->getDatasheet()->setItems($filteredItems);
    }

    protected function applySorting($sorting)
    {
        if (empty($sorting)) {
            $sorting = [
                'by' => '',
                'type' => '',
            ];
        }
        $this->getDatasheet()->setSorting($sorting);

        if (empty($sorting['by']) && empty($sorting['type'])) {
            return;
        }
        $this->getDatasheet()->setSorting($sorting);
        $items = $this->getDatasheet()->getItems();

        usort($items, function ($valueOne, $valueTwo) use ($sorting) {
            if (is_numeric($valueOne[$sorting['by']]) && is_numeric($valueTwo[$sorting['by']])) {
                return ($valueOne[$sorting['by']] <=> $valueTwo[$sorting['by']]) * ($sorting['type'] == 'DESC' ? -1 : 1);
            } else {
                return strcmp(mb_strtolower($valueOne[$sorting['by']]), mb_strtolower($valueTwo[$sorting['by']]));
            }
        });

        $this->getDatasheet()->setItems($items);
    }

    protected function addFilterChoices()
    {
        $choices = [];

        foreach ($this->getDatasheet()->getItems() as $item) {
            foreach ($item as $fieldName => $value) {
                if ($fieldName == 'id') {
                    continue;
                }

                if (strpos($fieldName, '.')) {
                    continue;
                }

                if (isset($choices[$fieldName]) && in_array($value, $choices[$fieldName])) {
                    continue;
                }
                $choices[$fieldName][] = $value;
            }
        }

        $fields = [];

        foreach ($this->getDatasheet()->getFields() as $fieldName => $field) {
            $fields[$fieldName] = $field;

            if ($fieldName == 'id') {
                continue;
            }

            if (strpos($fieldName, '.')) {
                continue;
            }
            $fields[$fieldName]['filterChoices'] = $choices[$fieldName];
            $fields[$fieldName]['hasFilter'] = true;
            $this->getDatasheet()->setHasFilters(true);
        }

        $this->getDatasheet()->setFields($fields);
    }
}