<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;

abstract class AbstractField implements FieldInterface
{
    const INDENT = "\t";

    protected $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): FieldInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return strtolower(StringUtility::getShortClassName($this, 'Field'));
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