<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Modifier\SchemaModifier;

class IntegerField extends AbstractField
{
    public function getName(): string
    {
        return 'Integer';
    }

    public function getFriendlyName(): string
    {
        return 'Number';
    }

    public function getMySQLCreateQuery(EntityField $object): string
    {
        return sprintf(
            'ALTER TABLE %s ADD %s INT DEFAULT NULL',
            SchemaModifier::toTableName($object->getEntity()->getName()),
            SchemaModifier::toFieldName($object->getName())
        );
    }

    public function getMySQLUpdateQuery(EntityField $entityFieldBefore, EntityField $entityFieldAfter): string
    {
        return sprintf(
            'ALTER TABLE %s CHANGE %s %s INT DEFAULT NULL',
            SchemaModifier::toTableName($entityFieldBefore->getEntity()->getName()),
            SchemaModifier::toFieldName($entityFieldBefore->getName()),
            SchemaModifier::toFieldName($entityFieldAfter->getName())
        );
    }
}