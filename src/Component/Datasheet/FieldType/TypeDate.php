<?php

namespace A2Global\CRMBundle\Component\Datasheet\FieldType;

use DateTimeInterface;

class TypeDate implements DataSheetFieldTypeInterface
{
    public function supports($value, $fieldOptions = []): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function get($value, $fieldOptions)
    {
        return $value ? $value->format('Y-m-d') : DataSheetFieldTypeInterface::VALUE_EMPTY;
    }
}