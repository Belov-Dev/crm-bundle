<?php

namespace A2Global\CRMBundle\Registry;

use A2Global\CRMBundle\Datasheet\DatasheetBuilder\DatasheetBuilderInterface;

class DatasheetBuilderRegistry
{
    protected $objects;

    public function __construct($objects)
    {
        $this->objects = $objects;
    }

    /**
     * @return DatasheetBuilderInterface[]
     */
    public function get()
    {
        return $this->objects;
    }
}