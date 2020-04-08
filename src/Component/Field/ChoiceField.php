<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;

class ChoiceField extends AbstractField implements FieldInterface, ConfigurableFieldInterface
{
    protected $options = [];

    public function addOption()
    {

    }

    public function getEntityClassProperty(): array
    {
        return [
            '/**',
            ' * @ORM\Column(type="string", length=255, nullable=true)',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ];
    }

    public function getConfigurationsFormControls(): string
    {
        return $this->render('@A2CRM/entity/field.choice.configuration.html.twig');
    }
}