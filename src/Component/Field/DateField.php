<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;
use DateTime;

class DateField extends AbstractField implements FieldInterface
{
    public function getEntityClassProperty(): array
    {
        return [
            '/**',
            ' * @ORM\Column(type="date", nullable=true)',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }

    public function getFormControl($value = null): string
    {
        return sprintf(
            '<div class="input-group"><div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>
            <input type="text" name="data[%s]" class="form-control" value="%s" placeholder="DD.MM.YYYY"></div>',
            StringUtility::toCamelCase($this->getName()),
            $value ? $value->format('d.m.Y') : null
        );
    }

    public function setValueToObject($value, $object): FieldInterface
    {
        $value = trim($value) ? new DateTime(preg_replace('/\D/', '-', trim($value))) : null;

        return parent::setValueToObject($value, $object);
    }
}