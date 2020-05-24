<?php

namespace A2Global\CRMBundle\Component\Datasheet\FieldType;

class TypeMoney implements DataSheetFieldTypeInterface
{
    public function supports($value, $fieldOptions = []): bool
    {
        return false;
    }

    public function get($value, $fieldOptions)
    {
        return [
            'value' => number_format($value, 0, '.', '’'),
            'class' => 'text-right',
        ];
    }
}