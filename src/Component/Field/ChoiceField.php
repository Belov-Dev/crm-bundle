<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Component\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Utility\StringUtility;

class ChoiceField extends AbstractField implements FieldInterface, ConfigurableFieldInterface
{
    protected $choices = [];

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function addChoice(string $value): self
    {
        $this->choices[] = $value;

        return $this;
    }

    public function setChoices(array $choices): self
    {
        $this->choices = $choices;

        return $this;
    }

    public function getConfigurationsFormControls(Entity $entity = null): string
    {
        return $this->render('@A2CRM/entity/field.choice.configuration.html.twig', [
            'choices' => $this->getChoices(),
        ]);
    }

    public function setConfigurationFromTheForm($configuration)
    {
        $choices = array_map(function($item){
            return StringUtility::normalize($item);
        }, $configuration['choices']);

        $this->setChoices($choices);
    }

    public function getEntityClassConstant(): array
    {
        $elements = [
            'const ' . StringUtility::toConstantName($this->name) . '_CHOICES = [',
        ];

        foreach ($this->choices as $choice) {
            $elements[] = self::INDENT . '\'' . $choice . '\',';
        }

        $elements[] = '];';

        return $elements;
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

    public function getFormControl($value = null): string
    {
        $html = [];
        $html[] = sprintf('<select class="form-control" name="data[%s]">', StringUtility::toCamelCase($this->getName()));

        foreach ($this->getChoices() as $option) {
            $isSelected = $value && ($value == $option);
            $html[] = sprintf('<option value="%s" %s>%s', $option, ($isSelected ? 'selected' : ''), $option);
        }
        $html[] = '</select>';

        return implode(PHP_EOL, $html);
    }
}