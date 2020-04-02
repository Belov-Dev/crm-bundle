<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Utility\StringUtility;

abstract class AbstractField implements EntityFieldInterface
{
    const INDENT = "\t";

    protected $names;

    public function getFriendlyName(): string
    {
        return $this->getName();
    }

    public function getFormControlHTML(EntityField $field, $value = null): string
    {
        return sprintf('<input type="text" name="field[%s]" class="form-control" autocomplete="off" value="%s">', StringUtility::toSnakeCase($field->getName()), htmlspecialchars($value));
    }

    public function getDoctrineClassPropertyCode(EntityField $object): array
    {
        return [
            '',
            '/**',
            '* @ORM\Column(type="' . StringUtility::toSnakeCase($this->getName()) . '", nullable=true)',
            '*/',
            'private $' . StringUtility::toCamelCase($object->getName()) . ';',
        ];
    }

    public function getDoctrineClassMethodsCode(EntityField $object): array
    {
        return [
            '',
            'public function get' . StringUtility::toPascalCase($object->getName()) . '()',
            '{',
            self::INDENT . 'return $this->' . StringUtility::toCamelCase($object->getName()) . ';',
            '}',
            '',
            'public function set' . StringUtility::toPascalCase($object->getName()) . '($value): self',
            '{',
            self::INDENT . '$this->' . StringUtility::toCamelCase($object->getName()) . ' = $value;',
            '',
            self::INDENT . 'return $this;',
            '}',
        ];
    }

    public function setValueToObject($object, EntityField $field, $value)
    {
        $setter = 'set' . StringUtility::toPascalCase($field->getName());

        return $object->{$setter}($value);
    }
}