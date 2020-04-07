<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityZ;
use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Utility\StringUtility;
use Twig\Environment;

class ChoiceField extends StringField implements EntityFieldConfigurableInterface
{
    /** @var Environment */
    protected $twig;

    /**
     * @required
     */
    public function setTwig(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getName(): string
    {
        return 'Choice';
    }

    public function getFormControlHTML(EntityField $field, $value = null): string
    {
        $html = [];
        $html[] = sprintf('<select class="form-control" name="field[%s]">', StringUtility::toSnakeCase($field->getName()));

        foreach ($field->getConfiguration()['choices'] as $option) {
            $isSelected = $value && ($value == $option);
            $html[] = sprintf('<option value="%s" %s>%s', $option, ($isSelected ? 'selected' : ''), $option);
        }
        $html[] = '</select>';

        return implode(PHP_EOL, $html);
    }

    public function getFormConfigurationControls(EntityZ $entity, $field)
    {
        return $this->twig->render('@A2CRM/entity/entity_field.choice.configuration.html.twig', [
            'entity' => $entity,
            'field' => $field,
        ]);
    }

    public function getFixtureValue($field)
    {
        $options = $field->getConfiguration()['choices'];

        return $options[array_rand($options)];
    }
}