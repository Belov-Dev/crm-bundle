<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\Entity;
use A2Global\CRMBundle\Entity\EntityField;

interface EntityFieldInterface
{
    public function getName(): string;

    public function getFriendlyName(): string;

    public function getMySQLCreateQuery(EntityField $object): string;

    public function getFormControlHTML($fieldName, $value = null): string;
}