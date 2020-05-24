<?php

namespace A2Global\CRMBundle\Registry;

use A2Global\CRMBundle\Component\Datasheet\FieldType\DataSheetFieldTypeInterface;

class DatasheetFieldRegistry
{
    protected $fieldTypes;

    public function __construct($fieldTypes)
    {
        $this->fieldTypes = $fieldTypes;
    }

    /** @return DataSheetFieldTypeInterface[] */
    public function findAll()
    {
        return $this->fieldTypes;
    }
}