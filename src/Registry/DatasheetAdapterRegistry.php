<?php

namespace A2Global\CRMBundle\Registry;

use A2Global\CRMBundle\Datasheet\Adapter\DatasheetAdapterInterface;

class DatasheetAdapterRegistry
{
    protected $objects;

    public function __construct($objects)
    {
        $this->objects = $objects;
    }

    /**
     * @return DatasheetAdapterInterface[]
     */
    public function get()
    {
        return $this->objects;
    }
}