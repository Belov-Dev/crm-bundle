<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Modifier\SchemaModifier;

class StringField extends AbstractField
{
    public function getName(): string
    {
        return 'String';
    }

    public function getMySQLCreateQuery(EntityField $object): string
    {
        return sprintf(
            'ALTER TABLE %s ADD %s VARCHAR(255) DEFAULT NULL',
            SchemaModifier::toTableName($object->getEntity()->getName()),
            SchemaModifier::toFieldName($object->getName())
        );
    }
}