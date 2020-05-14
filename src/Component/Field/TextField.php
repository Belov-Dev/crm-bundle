<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;

class TextField extends AbstractField implements FieldInterface
{
    public function getEntityClassProperty(): array
    {
        return [
            '/**',
            ' * @ORM\Column(type="text", nullable=true)',
            ' */',
            'private $' . StringUtility::toSnakeCase($this->getName()) . ';',
        ];
    }
}