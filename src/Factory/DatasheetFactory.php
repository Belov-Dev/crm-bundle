<?php

namespace A2Global\CRMBundle\Factory;

use A2Global\CRMBundle\Datasheet\Datasheet;
use A2Global\CRMBundle\Datasheet\DatasheetExtended;

class DatasheetFactory
{
    public function createNew(): Datasheet
    {
        return new DatasheetExtended();
    }
}