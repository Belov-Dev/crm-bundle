<?php

namespace A2Global\CRMBundle\Entity;

use A2Global\CRMBundle\FieldType\FieldTypeInterface;

class Entity
{
    protected $name;

    /** @var FieldTypeInterface[] */
    protected $fields = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addField(FieldTypeInterface $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return FieldTypeInterface[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}