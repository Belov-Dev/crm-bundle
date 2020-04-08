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
}