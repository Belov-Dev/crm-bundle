<?php

namespace A2Global\CRMBundle\Component\Entity;

use A2Global\CRMBundle\Component\Field\FieldInterface;
use A2Global\CRMBundle\Utility\ArrayUtility;
use A2Global\CRMBundle\Utility\StringUtility;
use Exception;

class Entity
{
    protected $name;

    protected $tableName;

    /** @var FieldInterface[] */
    protected $fields = [];

    public function __construct(string $name = null)
    {
        $this->name = $name;
    }

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
     * @return FieldInterface[]
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

    public function updateField($fieldName, FieldInterface $field): self
    {
        $newFieldName = StringUtility::toCamelCase($field->getName());
        $this->fields = ArrayUtility::renameKey($this->fields, $fieldName, $newFieldName);
        $this->fields[$newFieldName] = $field;

        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }
}