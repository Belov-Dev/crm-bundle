<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;
use DateTime;

class DatetimeField extends AbstractField implements FieldInterface
{
    public function getEntityClassProperty(): array
    {
        return [
            '/**',
            ' * @ORM\Column(type="datetime", nullable=true)',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }

    public function getFormControl($value = null): string
    {
        return sprintf(
            '<div class="input-group"><div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>
            <input type="datetime-local" name="data[%s]" class="form-control" value="%s" placeholder="dd-mm-yyyy hh:ii:ss" maxlength="10"></div>',
            StringUtility::toCamelCase($this->getName()),
            $value ? $value->format('Y-m-d') . 'T' . $value->format('H:i') : null
        );
    }

    public function setValueToObject($value, $object): FieldInterface
    {
        $value = new DateTime($value);

        return parent::setValueToObject($value, $object);
    }
}