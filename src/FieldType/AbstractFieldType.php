<?php

namespace A2Global\CRMBundle\FieldType;

use A2Global\CRMBundle\Utility\StringUtility;

abstract class AbstractFieldType implements FieldTypeInterface
{
    const INDENT = "\t";

    protected $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEntityClassProperty(): array
    {
        return  [];
    }

    public function getEntityClassMethods(): array
    {
        return [
            '',
            'public function get' . StringUtility::toPascalCase($this->getName()) . '()',
            '{',
            self::INDENT . 'return $this->' . StringUtility::toCamelCase($this->getName()) . ';',
            '}',
            '',
            'public function set' . StringUtility::toPascalCase($this->getName()) . '($value): self',
            '{',
            self::INDENT . '$this->' . StringUtility::toCamelCase($this->getName()) . ' = $value;',
            '',
            self::INDENT . 'return $this;',
            '}',
        ];
    }
}