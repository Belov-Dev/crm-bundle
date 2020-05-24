<?php

namespace A2Global\CRMBundle\Component\Datasheet\FieldType;

interface DataSheetFieldTypeInterface
{
    const VALUE_EMPTY = '&nbsp;';

    public function supports($value, $fieldOptions = []): bool;

    public function get($value, $fieldOptions);
}