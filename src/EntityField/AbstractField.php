<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\Entity;

abstract class AbstractField implements EntityFieldInterface
{
    protected $names;

    public function getFriendlyName(): string
    {
        return $this->getName();
    }

    public function getFormControlHTML($fieldName, $value = null): string
    {
        return sprintf('<input type="text" name="field[%s]" class="form-control" autocomplete="off" value="%s">', $fieldName, htmlspecialchars($value));
    }

    public function getExtendedFormControls(Entity $entity, $object = null)
    {
        return null;
    }
}