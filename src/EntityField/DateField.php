<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityField;
use A2Global\CRMBundle\Modifier\SchemaModifier;
use A2Global\CRMBundle\Utility\StringUtility;
use DateTime;

class DateField extends AbstractField
{
    public function getName(): string
    {
        return 'Date';
    }

    public function getMySQLCreateQuery(EntityField $object): string
    {
        return sprintf(
            'ALTER TABLE %s ADD %s DATE DEFAULT NULL',
            SchemaModifier::toTableName($object->getEntity()->getName()),
            SchemaModifier::toFieldName($object->getName())
        );
    }

    public function getMySQLUpdateQuery(EntityField $entityFieldBefore, EntityField $entityFieldAfter): string
    {
        return sprintf(
            'ALTER TABLE %s CHANGE %s %s DATE DEFAULT NULL',
            SchemaModifier::toTableName($entityFieldBefore->getEntity()->getName()),
            SchemaModifier::toFieldName($entityFieldBefore->getName()),
            SchemaModifier::toFieldName($entityFieldAfter->getName())
        );
    }

    public function getFormControlHTML(EntityField $field, $value = null): string
    {
        return sprintf(
            '<div class="input-group"><div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" name="field[%s]" class="form-control" value="%s" data-date-mask="" placeholder="dd/mm/yyyy" maxlength="10"></div>',
            StringUtility::toSnakeCase($field->getName()),
            $value ? htmlspecialchars($value->format('d/m/Y')) : null
        );
    }

    public function setValueToObject($object, EntityField $field, $value)
    {
        $setter = 'set'.StringUtility::toPascalCase($field->getName());

        return $object->{$setter}(new DateTime($value));
    }
}