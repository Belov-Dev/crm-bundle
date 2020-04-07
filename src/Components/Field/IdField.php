<?php

namespace A2Global\CRMBundle\Components\Field;

class IdField extends AbstractField implements FieldInterface
{
    public function getEntityClassProperty(): array
    {
        return [
            '/**',
            ' * @ORM\Id()',
            ' * @ORM\GeneratedValue()',
            ' * @ORM\Column(type="integer")',
            ' */',
            'private $id;',
        ];
    }

    public function getEntityClassMethods(): array
    {
        return [
            '',
            'public function getId()',
            '{',
            self::INDENT . 'return $this->id;',
            '}',
        ];
    }
}