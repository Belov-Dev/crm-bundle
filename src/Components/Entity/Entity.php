<?php

namespace A2Global\CRMBundle\Components\Entity;

use A2Global\CRMBundle\Components\Field\FieldInterface;
use A2Global\CRMBundle\Utility\StringUtility;
use Exception;

class Entity
{
    protected $name;

    /** @var FieldInterface[] */
    protected $fields = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function addField(FieldInterface $field): self
    {
        $this->fields[StringUtility::toCamelCase($field->getName())] = $field;

        return $this;
    }

    /**
     * @return FieldTypeInterface[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField($name): FieldInterface
    {
        return $this->fields[StringUtility::toCamelCase($name)];
    }

    public function removeField($name)
    {
        if (!array_key_exists($name, $this->fields)) {
            throw new Exception(sprintf('Field %s in entity %s not found', $name, $this->getName()));
        }
        unset($this->fields[$name]);
    }
}