<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\DatasheetExtended;
use A2Global\CRMBundle\Utility\StringUtility;
use Doctrine\ORM\Query\Expr\From;

class ArrayAdapter implements DatasheetAdapterInterface
{
    public function supports(DatasheetExtended $datasheet): bool
    {
        return !$datasheet->getQueryBuilder();
    }

    public function getItems(DatasheetExtended $datasheet): array
    {
        if (is_callable($datasheet->getData())) {
            $callable = $datasheet->getData();
            $datasheet->setData(
                $callable($datasheet->getItemsPerPage(), $datasheet->getPage() * $datasheet->getItemsPerPage())
            );
        }

        return $datasheet->getData();
    }

    public function getItemsTotal(DatasheetExtended $datasheet): int
    {
        return count($datasheet->getData());
    }

    public function getFields(DatasheetExtended $datasheet): array
    {
        $fields = [];

        if ($datasheet->getItems() && count($datasheet->getItems()) > 0) {
            foreach (array_keys($datasheet->getItems()[0]) as $fieldName) {
                $fields[StringUtility::toCamelCase($fieldName)] = [
                    'title' => StringUtility::normalize($fieldName),
                ];
            }
        }

        return $fields;
    }
}