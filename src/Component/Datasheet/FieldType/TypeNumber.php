<?php

namespace A2Global\CRMBundle\Component\Datasheet\FieldType;

class TypeNumber implements DataSheetFieldTypeInterface
{
    public function supports($value, $fieldOptions = []): bool
    {
        return ((string)(floatval($value)) === (string)$value) || ((string)(intval($value) === (string)$value));
    }

    public function get($value, $fieldOptions)
    {
        return [
            'value' => $value,
            'class' => 'text-right',
        ];
    }
}