<?php

namespace A2Global\CRMBundle\Component\Field;

class IdField extends AbstractField implements FieldInterface
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