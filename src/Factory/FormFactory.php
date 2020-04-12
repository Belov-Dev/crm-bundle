<?php

namespace A2Global\CRMBundle\Factory;

use A2Global\CRMBundle\Component\Entity\Entity;
use A2Global\CRMBundle\Component\Field\FieldInterface;
use A2Global\CRMBundle\Component\Form\Form;
use A2Global\CRMBundle\Provider\EntityInfoProvider;
use A2Global\CRMBundle\Utility\StringUtility;

class FormFactory
{
    private $entityInfoProvider;

    public function __construct(
        EntityInfoProvider $entityInfoProvider
    )
    {
        $this->entityInfoProvider = $entityInfoProvider;
    }

    public function getFor($object): Form
    {
        $form = new Form();
        $entity = $this->entityInfoProvider->getEntity(StringUtility::getShortClassName($object));

        /** @var FieldInterface $field */
        foreach($entity->getFields() as $field){
            $name = StringUtility::toCamelCase($field->getName());

            if($name == 'id'){
                continue;
            }
            $value = $object->{'get'.$name}();
            $form->addField($field->getName(), $field->getFormControl($value));
        }

        return $form;
    }
}