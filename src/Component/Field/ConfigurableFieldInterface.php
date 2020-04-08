<?php

namespace A2Global\CRMBundle\Component\Field;

interface ConfigurableFieldInterface
{
    public function getConfigurationsFormControls(): string;
}