<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;

class FloatField extends AbstractField implements FieldInterface
{
    public function getEntityClassProperty(): array
    {
        return [
            '/**',
            ' * @ORM\Column(type="float", nullable=true)',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }

    public function setValueToObject($value, $object): FieldInterface
    {
        return parent::setValueToObject((float)$value, $object);
    }
}