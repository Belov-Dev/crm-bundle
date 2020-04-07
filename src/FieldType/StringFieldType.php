<?php

namespace A2Global\CRMBundle\FieldType;

use A2Global\CRMBundle\Utility\StringUtility;

class StringFieldType extends AbstractFieldType implements FieldTypeInterface
{
    protected $name = 'string';

    public function getEntityClassProperty(): array
    {
        return [
            '',
            '/**',
            '* @ORM\Column(type="string", length=255, nullable=true)',
            '*/',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }
}