<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use A2Global\CRMBundle\Utility\StringUtility;

class StringField extends AbstractField
{
    public function getName(): string
    {
        return 'String';
    }

    public function getDoctrineClassPropertyCode(EntityField $object): array
    {
        return [
            '',
            '/**',
            '* @ORM\Column(type="string", length=255, nullable=true)',
            '*/',
            'private $' . StringUtility::toCamelCase($object->getName()) . ';',
        ];
    }

    public function getMySQLCreateQuery(EntityField $object): string
    {
        return sprintf(
            'ALTER TABLE %s ADD %s VARCHAR(255) DEFAULT NULL',
            SchemaModifier::toTableName($object->getEntity()->getName()),
            SchemaModifier::toFieldName($object->getName())
        );
    }

    public function getMySQLUpdateQuery(EntityField $entityFieldBefore, EntityField $entityFieldAfter): string
    {
        return sprintf(
            'ALTER TABLE %s CHANGE %s %s VARCHAR(255) DEFAULT NULL',
            SchemaModifier::toTableName($entityFieldBefore->getEntity()->getName()),
            SchemaModifier::toFieldName($entityFieldBefore->getName()),
            SchemaModifier::toFieldName($entityFieldAfter->getName())
        );
    }
}