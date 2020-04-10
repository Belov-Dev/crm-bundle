<?php

namespace A2Global\CRMBundle\Component\Field;

use A2Global\CRMBundle\Component\Entity\Entity;

interface ConfigurableFieldInterface
{
    public function getConfigurationsFormControls(Entity $entity = null): string;
}