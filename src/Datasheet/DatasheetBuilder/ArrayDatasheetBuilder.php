<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Utility\StringUtility;

class ArrayDatasheetBuilder extends AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    public function supports(): bool
    {
        return is_array($this->getDatasheet()->getData()) || is_callable($this->getDatasheet()->getData());
    }

    public function build($page = null, $itemsPerPage = null, $filters = [])
    {
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
                    $this->getDatasheet()->getItemsPerPage() * ($this->getDatasheet()->getPage() - 1)
                )
            );
        } else {
            $this->getDatasheet()->setItems($this->getDatasheet()->getData());
        }

        if (!$this->getDatasheet()->getItemsTotal() && count($this->getDatasheet()->getItems())) {
            $this->getDatasheet()->setItemsTotal(count($this->getDatasheet()->getItems()));
        }
        $fields = [];

        if ($this->getDatasheet()->getFieldsToShow()) {
            foreach ($this->getDatasheet()->getFieldsToShow() as $fieldName) {
                $fieldName = StringUtility::toCamelCase($fieldName);
                $fields[$fieldName] = [
                    'title' => $this->getDatasheet()->getFieldOptions()[$fieldName]['title'] ?? StringUtility::normalize($fieldName),
                    'hasFilter' => false,
                ];
            }
        } else {
            if ($this->datasheet->getItems() && count($this->datasheet->getItems()) > 0) {
                foreach (array_keys($this->datasheet->getItems()[0]) as $fieldName) {
                    $fieldName = StringUtility::toCamelCase($fieldName);
                    $fields[$fieldName] = [
                        'title' => $this->getDatasheet()->getFieldOptions()[$fieldName]['title'] ?? StringUtility::normalize($fieldName),
                        'hasFilter' => false,
                    ];
                }
            }
        }

        foreach ($this->getDatasheet()->getFieldsToRemove() as $fieldToRemove) {
            unset($fields[$fieldToRemove]);
        }

        $this->getDatasheet()->setFields($fields);

        parent::build($page, $itemsPerPage, $filters);
    }
}