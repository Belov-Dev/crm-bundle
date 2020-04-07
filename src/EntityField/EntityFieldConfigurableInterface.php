<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Entity\EntityZ;

interface EntityFieldConfigurableInterface
{
    public function getFormConfigurationControls(EntityZ $entity, $field);
}