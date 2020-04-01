<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use A2Global\CRMBundle\Utility\StringUtility;

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

    public function getMySQLUpdateQuery(EntityField $entityFieldBefore, EntityField $entityFieldAfter): string
    {
        return sprintf(
            'ALTER TABLE %s CHANGE %s %s TINYINT(1) DEFAULT NULL',
            SchemaModifier::toTableName($entityFieldBefore->getEntity()->getName()),
            SchemaModifier::toFieldName($entityFieldBefore->getName()),
            SchemaModifier::toFieldName($entityFieldAfter->getName())
        );
    }

    public function getFormControlHTML(EntityField $field, $value = null): string
    {
        $html = [];
        $html[] = '<label class="radio-inline">';
        $html[] = sprintf(
            '<input type="radio" name="field[%s]" value="1" %s> Yes',
            StringUtility::toSnakeCase($field->getName()),
            (is_null($value) || (bool)$value) ? 'checked' : ''
        );
        $html[] = '</label>';
        $html[] = '<label class="radio-inline">';
        $html[] = sprintf(
            '<input type="radio" name="field[%s]" value="0" %s> Nope',
            StringUtility::toSnakeCase($field->getName()),
            (bool)$value ? '' : 'checked'
        );
        $html[] = '</label>';

        return implode(PHP_EOL, $html);
    }

    public function setValueToObject($object, EntityField $field, $value)
    {
        $setter = 'set' . StringUtility::toPascalCase($field->getName());

        return $object->{$setter}((bool)$value);
    }
}