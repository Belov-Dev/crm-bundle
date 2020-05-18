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
            <input type="date" name="data[%s]" class="form-control" value="%s" placeholder="dd-mm-yyyy" maxlength="10"></div>',
            StringUtility::toCamelCase($this->getName()),
            $value ? htmlspecialchars($value->format('Y-m-d')) : null
        );
    }

    public function setValueToObject($value, $object): FieldInterface
    {
        $value = new DateTime($value);

        return parent::setValueToObject($value, $object);
    }
}