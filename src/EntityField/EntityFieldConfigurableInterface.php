<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\Entity;

interface EntityFieldConfigurableInterface
{
    public function getFormConfigurationControls(Entity $entity, $object = null);
}