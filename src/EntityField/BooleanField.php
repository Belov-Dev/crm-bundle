<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Modifier\SchemaModifier;

class BooleanField extends AbstractField
{
    public function getName(): string
    {
        return 'Boolean';
    }

    public function getFriendlyName(): string
    {
        return 'Boolean: True/False';
    }


    public function getMySQLCreateQuery(EntityField $object): string
    {
        return sprintf(
            'ALTER TABLE %s ADD %s TINYINT(1) DEFAULT NULL',
            SchemaModifier::toTableName($object->getEntity()->getName()),
            SchemaModifier::toFieldName($object->getName())
        );
    }
}