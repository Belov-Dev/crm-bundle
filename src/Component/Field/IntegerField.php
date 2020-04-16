<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;

class IntegerField extends AbstractField implements FieldInterface
{
    public function getEntityClassProperty(): array
    {
        return [
            '/**',
            ' * @ORM\Column(type="integer", nullable=true)',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }
}