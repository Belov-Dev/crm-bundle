<?php

namespace A2Global\CRMBundle\Factory;

use A2Global\CRMBundle\Datasheet\Datasheet;

class DatasheetFactory
{
    public function get()
    {
        return new Datasheet();
    }
}