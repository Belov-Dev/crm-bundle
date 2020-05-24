<?php

namespace A2Global\CRMBundle\Component\Datasheet\FieldType;

class TypeString implements DataSheetFieldTypeInterface
{
    public function supports($value, $fieldOptions = []): bool
    {
        return false;
    }

    public function get($value, $fieldOptions)
    {
        return $value ?: DataSheetFieldTypeInterface::VALUE_EMPTY;
    }
}