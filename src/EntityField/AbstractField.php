<?php

namespace A2Global\CRMBundle\EntityField;

use A2Global\CRMBundle\Utility\StringUtility;

abstract class AbstractField implements EntityFieldInterface
{
    protected $names;

    public function getFriendlyName(): string
    {
        return $this->getName();
    }
}