<?php

namespace A2Global\CRMBundle\FieldType;

class IDFieldType extends AbstractFieldType
{
    protected $name = 'id';

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