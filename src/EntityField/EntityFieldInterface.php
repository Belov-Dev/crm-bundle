<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityField;

interface EntityFieldInterface
{
    public function getName(): string;

    public function getFriendlyName(): string;

    public function getMySQLCreateQuery(EntityField $object): string;

    public function getFormControlHTML(EntityField $field, $value = null): string;

    public function getDoctrineClassPropertyCode(EntityField $object): array;

    public function getDoctrineClassMethodsCode(EntityField $object): array;

    public function setValueToObject($object, EntityField $field, $value);
}