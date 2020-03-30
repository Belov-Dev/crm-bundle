<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\Entity;

interface EntityFieldInterface
{
    public function getName(): string;

    public function getFriendlyName(): string;

    public function getMySQLFieldType(): string;

    public function getFormControlHTML($fieldName, $value = null): string;

    public function getExtendedFormControls(Entity $entity, $object = null);
}