<?php

namespace A2Global\CRMBundle\Datasheet\DatasheetBuilder;

use A2Global\CRMBundle\Utility\StringUtility;

class ArrayDatasheetBuilder extends AbstractDatasheetBuilder implements DatasheetBuilderInterface
{
    public function supports(): bool
    {
        return is_array($this->getDatasheet()->getData());
    }

    public function getItems(): array
    {
        if (is_callable($this->datasheet->getData())) {
            $callable = $this->datasheet->getData();
            $this->datasheet->setData(
                $callable(
                    $this->datasheet->getItemsPerPage(),
                    $this->datasheet->getPage() * $this->datasheet->getItemsPerPage()
                )
            );
        }

        return $this->datasheet->getData();
    }

    public function getItemsTotal(): int
    {
        return count($this->datasheet->getData());
    }

    public function getFields(): array
    {
        $fields = [];

        if ($this->datasheet->getItems() && count($this->datasheet->getItems()) > 0) {
            foreach (array_keys($this->datasheet->getItems()[0]) as $fieldName) {
                $fields[StringUtility::toCamelCase($fieldName)] = [
                    'title' => StringUtility::normalize($fieldName),
                    'hasFilter' => false,
                ];
            }
        }

        return $fields;
    }
}