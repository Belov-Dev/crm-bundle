<?php

namespace A2Global\CRMBundle\Datasheet\Adapter;

use A2Global\CRMBundle\Datasheet\Datasheet;
use A2Global\CRMBundle\Utility\StringUtility;

class ComplexQueryBuilderAdapter implements DatasheetAdapterInterface
{
    public function supports(Datasheet $datasheet): bool
    {
        return count($datasheet->queryBuilder->getDQLPart('select')) > 1;
    }

    public function getFields(Datasheet $datasheet)
    {
        $fields = [];

        foreach ($datasheet->queryBuilder->getDQLPart('select') as $select) {
            $field = $select->getParts()[0];
            $tmp = explode('.', $field);

            if (count($tmp) < 2) {
                continue;
            }
            $fields[StringUtility::toCamelCase(trim($tmp[1]))] = [
                'title' => StringUtility::toCamelCase(trim($tmp[1])),
            ];
        }

        return $fields;
    }
}