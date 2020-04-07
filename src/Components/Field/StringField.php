<?php

namespace A2Global\CRMBundle\Components\Field;

use A2Global\CRMBundle\Utility\StringUtility;

class StringField extends AbstractField implements FieldInterface
{
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