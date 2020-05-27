<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;

class BooleanField extends AbstractField implements FieldInterface
{
    public function getEntityClassProperty(): array
    {
        return [
            '/**',
            ' * @ORM\Column(type="boolean", nullable=true)',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }

    public function getFormControl($value = null): string
    {
        return sprintf(
            '<input type="checkbox" name="data[%s]" value="1" %s>',
            StringUtility::toCamelCase($this->getName()),
            (is_null($value) || (bool)$value) ? 'checked' : ''
        );
    }

    public function getFormControlOld($value = null): string
    {
        $html = [];
        $html[] = '<label class="radio-inline">';
        $html[] = sprintf(
            '<input type="radio" name="data[%s]" value="1" %s> Yes',
            StringUtility::toCamelCase($this->getName()),
            (is_null($value) || (bool)$value) ? 'checked' : ''
        );
        $html[] = '</label>';
        $html[] = '<label class="radio-inline">';
        $html[] = sprintf(
            '<input type="radio" name="data[%s]" value="0" %s> Nope',
            StringUtility::toSnakeCase($this->getName()),
            (bool)$value ? '' : 'checked'
        );
        $html[] = '</label>';

        return implode(PHP_EOL, $html);
    }

    public function setValueToObject($value, $object): FieldInterface
    {
        return parent::setValueToObject((bool)$value, $object);
    }
}