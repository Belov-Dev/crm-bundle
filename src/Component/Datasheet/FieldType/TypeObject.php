<?php

namespace A2Global\CRMBundle\Component\Datasheet\FieldType;

use A2Global\CRMBundle\Utility\StringUtility;

class TypeObject implements DataSheetFieldTypeInterface
{
    public function supports($value, $fieldOptions = []): bool
    {

        return is_object($value);
    }

    public function get($value, $fieldOptions)
    {
        if (method_exists($value, '__toString')) {
            return (string)$value;
        }

        return StringUtility::normalize(StringUtility::getShortClassName($value)) . ' #' . $value->getId();
    }
}