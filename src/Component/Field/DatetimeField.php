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
            ' * @ORM\Column(type="date", nullable=true)',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }

    public function getFormControl($value = null): string
    {
        return sprintf(
            '<div class="input-group"><div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" name="data[%s]" class="form-control" value="%s" data-date-mask="" placeholder="dd/mm/yyyy" maxlength="10"></div>',
            StringUtility::toSnakeCase($this->getName()),
            $value ? htmlspecialchars($value->format('d/m/Y')) : null
        );
    }

    public function setValueToObject($value, $object): FieldInterface
    {
        $value = new DateTime(str_replace('/', '-', $value));

        return parent::setValueToObject($value, $object);
    }
}