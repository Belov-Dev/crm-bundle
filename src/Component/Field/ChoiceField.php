<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Utility\StringUtility;

class ChoiceField extends AbstractField implements FieldInterface, ConfigurableFieldInterface
{
    protected $choices = [];

    public function addChoice($value)
    {
        $this->choices[] = $value;
    }

    public function setConfigurationFromTheForm($configuration)
    {
        foreach ($configuration['choices'] as $choice) {
            $this->addChoice(StringUtility::normalize($choice));
        }
    }

    public function getEntityClassProperty(): array
    {
        $elements = [
            'const ' . StringUtility::toConstantName($this->name) . '_CHOICES = [',
        ];
        $choices = array_map(function ($item) {
            return self::INDENT . '\'' . $item . '\',';
        }, $this->choices);
        $elements = array_merge($elements, $choices);

        return array_merge($elements, [
            '];',
            '',
            '/**',
            ' * @ORM\Column(type="string", length=255, nullable=true)',
            ' */',
            'private $' . StringUtility::toCamelCase($this->getName()) . ';',
        ]);
    }

    public function getConfigurationsFormControls(): string
    {
        return $this->render('@A2CRM/entity/field.choice.configuration.html.twig');
    }
}