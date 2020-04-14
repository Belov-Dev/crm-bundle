<?php

namespace A2Global\CRMBundle\Datasheet;

use A2Global\CRMBundle\Provider\EntityInfoProvider;

trait DatasheetDependencyInjectionTrait
{
    protected $entityInfoProvider;

    protected $itemsSourceCallable;

    public function __construct(
        EntityInfoProvider $entityInfoProvider
    )
    {
        $this->entityInfoProvider = $entityInfoProvider;
    }
}