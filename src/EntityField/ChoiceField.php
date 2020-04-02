<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;
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
//        $configuration$field->getConfiguration()
//        $targetEntity = $this->getTargetEntity($field);
//        $optionsRepository = $this->entityManager->getRepository('App:' . StringUtility::toPascalCase($targetEntity->getName()));
//
//        $html = [];
//        $html[] = sprintf('<select class="form-control" name="field[%s]">', StringUtility::toSnakeCase($field->getName()));
//
//        foreach ($optionsRepository->findAll() as $item) {
//            $isSelected = $value && ($value->getId() == $item->getId());
//            $html[] = sprintf('<option value="%s" %s>%s', $item->getId(), ($isSelected ? 'selected' : ''), (string)$item);
//        }
//        $html[] = '</select>';
//
//        return implode(PHP_EOL, $html);
    }

    public function getFormConfigurationControls(Entity $entity, $field)
    {
        return $this->twig->render('@A2CRM/entity/entity_field.choice.configuration.html.twig', [
            'entity' => $entity,
            'field' =>  $field,
        ]);
    }
}