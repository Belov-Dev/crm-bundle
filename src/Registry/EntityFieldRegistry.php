<?php

namespace A2Global\CRMBundle\Registry;

use A2Global\CRMBundle\EntityField\EntityFieldInterface;
use A2Global\CRMBundle\Utility\StringUtility;

class EntityFieldRegistry
{
    protected $fieldTypes;

    public function __construct($fieldTypes)
    {
        $this->normalize($fieldTypes);
    }

    public function find($name): EntityFieldInterface
    {
        if (!array_key_exists($name, $this->fieldTypes)) {
            throw new \Exception('Failed to find entity field by name: ' . $name);
        }

        return $this->fieldTypes[$name];
    }

    public function findAll(): array
    {
        return $this->fieldTypes;
    }

    /**
     * Returns for ChoiceType::class for FormType::class
     * [
     *      'User friendly name' => camelCase',
     *      'String' => string',
     *      'Digit' => integer',
     * ]
     *
     * @return array
     */
    public function getFormFieldChoices(): array
    {
        $choices = [];

        /** @var EntityFieldInterface $fieldType */
        foreach ($this->fieldTypes as $fieldNameCamelCase => $fieldType) {
            $choices[$fieldType->getFriendlyName()] = $fieldNameCamelCase;
        }

        return $choices;
    }

    protected function normalize($fieldTypes)
    {
        /** @var EntityFieldInterface $fieldType */
        foreach ($fieldTypes as $fieldType) {
            $this->fieldTypes[StringUtility::toCamelCase($fieldType->getName())] = $fieldType;
        }
    }
}