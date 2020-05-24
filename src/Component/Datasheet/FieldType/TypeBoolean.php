<?php

namespace A2Global\CRMBundle\Component\Datasheet\FieldType;

class TypeBoolean implements DataSheetFieldTypeInterface
{
    public function supports($value, $fieldOptions = []): bool
    {
        return is_bool($value);
    }

    public function get($value, $fieldOptions)
    {
        return $value ? '<span class="badge bg-light-blue">Yes</span>' : '<span class="badge">No</span>';
    }
}