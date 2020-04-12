<?php

namespace A2Global\CRMBundle\Factory;

use A2Global\CRMBundle\Datasheet\Datasheet;
use A2Global\CRMBundle\Provider\EntityInfoProvider;

class DatasheetFactory
{
    private $entityInfoProvider;

    public function __construct(EntityInfoProvider $entityInfoProvider)
    {
        $this->entityInfoProvider = $entityInfoProvider;
    }

    public function get()
    {
        return new Datasheet($this->entityInfoProvider);
    }
}